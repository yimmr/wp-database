<?php

namespace Impack\WP\ErrPage;

use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;
use Whoops\Handler\Handler;
use Whoops\Util\Misc;

class PageHandler extends Handler
{
    const EDITOR_SUBLIME  = "sublime";
    const EDITOR_TEXTMATE = "textmate";
    const EDITOR_EMACS    = "emacs";
    const EDITOR_MACVIM   = "macvim";
    const EDITOR_PHPSTORM = "phpstorm";
    const EDITOR_IDEA     = "idea";
    const EDITOR_VSCODE   = "vscode";
    const EDITOR_ATOM     = "atom";
    const EDITOR_ESPRESSO = "espresso";
    const EDITOR_XDEBUG   = "xdebug";

    private $searchPaths = [];

    private $resourceCache = [];

    private $extraTables = [];

    private $handleUnconditionally = false;

    private $pageTitle = "Whoops! There was an error.";

    private $applicationPaths;

    private $blacklist = [
        '_GET'     => [],
        '_POST'    => [],
        '_FILES'   => [],
        '_COOKIE'  => [],
        '_SESSION' => [],
        '_SERVER'  => [],
        '_ENV'     => [],
    ];

    protected $editor;

    protected $editors = [
        "sublime"  => "subl://open?url=file://%file&line=%line",
        "textmate" => "txmt://open?url=file://%file&line=%line",
        "emacs"    => "emacs://open?url=file://%file&line=%line",
        "macvim"   => "mvim://open/?url=file://%file&line=%line",
        "phpstorm" => "phpstorm://open?file=%file&line=%line",
        "idea"     => "idea://open?file=%file&line=%line",
        "vscode"   => "vscode://file/%file:%line",
        "atom"     => "atom://core/open/file?filename=%file&line=%line",
        "espresso" => "x-espresso://open?filepath=%file&lines=%line",
    ];

    protected static $loadedCss;

    protected static $loadedJs;

    protected static $pageNum = 0;

    public function __construct()
    {
        if (ini_get('xdebug.file_link_format') || extension_loaded('xdebug')) {
            $this->editors['xdebug'] = function ($file, $line) {
                return str_replace(['%f', '%l'], [$file, $line], ini_get('xdebug.file_link_format'));
            };

            $this->setEditor('xdebug');
        }

        $this->searchPaths[] = __DIR__ . "/Resources";

        $this->blacklist('_SERVER', 'PHP_AUTH_PW');

        $this->setEditor('vscode');
    }

    /**
     * @return int|null
     */
    public function handle()
    {
        $inspector = $this->getInspector();
        $frames    = $this->getExceptionFrames();
        $code      = $this->getExceptionCode();

        // List of variables that will be passed to the layout template.
        $vars = [
            "page_title"       => $this->getPageTitle(),
            "title"            => $this->getPageTitle(),
            "name"             => explode("\\", $inspector->getExceptionName()),
            "message"          => $inspector->getExceptionMessage(),
            "previousMessages" => $inspector->getPreviousExceptionMessages(),
            "docref_url"       => $inspector->getExceptionDocrefUrl(),
            "code"             => $code,
            "previousCodes"    => $inspector->getPreviousExceptionCodes(),
            "frames"           => $frames,
            "has_frames"       => !!count($frames),
            "handler"          => $this,
            "page_num"         => static::$pageNum += 1,

            "tables"           => [
                "GET Data"              => $this->masked($_GET, '_GET'),
                "POST Data"             => $this->masked($_POST, '_POST'),
                "Files"                 => isset($_FILES) ? $this->masked($_FILES, '_FILES') : [],
                "Cookies"               => $this->masked($_COOKIE, '_COOKIE'),
                "Session"               => isset($_SESSION) ? $this->masked($_SESSION, '_SESSION') : [],
                "Server/Request Data"   => $this->masked($_SERVER, '_SERVER'),
                "Environment Variables" => $this->masked($_ENV, '_ENV'),
            ],
        ];

        $extraTables = array_map(function ($table) use ($inspector) {
            return $table instanceof \Closure ? $table($inspector) : $table;
        }, $this->getDataTables());

        $vars["tables"] = array_merge($extraTables, $vars["tables"]);

        \extract($vars);

        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body>';

        $this->loadPageCss();
        $this->loadPagejs();

        include $this->getResource('view/main.php');

        echo '</body></html>';

        return Handler::QUIT;
    }

    public function loadPageCss()
    {
        if (!self::$loadedCss) {
            self::$loadedCss = true;
            echo '<style>';
            include $this->getResource('css/style.css');
            echo '</style>';
        }
    }

    public function loadPagejs()
    {
        if (!self::$loadedJs) {
            self::$loadedJs = true;
            echo '<script>';
            include $this->getResource('js/script.js');
            echo '</script>';
        }
    }

    public function escape($raw)
    {
        $flags = ENT_QUOTES;

        if (defined("ENT_SUBSTITUTE") && !defined("HHVM_VERSION")) {
            $flags |= ENT_SUBSTITUTE;
        } else {
            $flags |= ENT_IGNORE;
        }

        $raw = str_replace(chr(9), '    ', $raw);

        return htmlspecialchars($raw, $flags, "UTF-8");
    }

    /**
     * Get the stack trace frames of the exception currently being handled.
     *
     * @return \Whoops\Exception\FrameCollection
     */
    protected function getExceptionFrames()
    {
        $frames = $this->getInspector()->getFrames();

        if ($this->getApplicationPaths()) {
            foreach ($frames as $frame) {
                foreach ($this->getApplicationPaths() as $path) {
                    if (strpos($frame->getFile(), $path) === 0) {
                        $frame->setApplication(true);
                        break;
                    }
                }
            }
        }

        return $frames;
    }

    /**
     * Get the code of the exception currently being handled.
     *
     * @return string
     */
    protected function getExceptionCode()
    {
        $exception = $this->getException();

        $code = $exception->getCode();
        if ($exception instanceof \ErrorException) {
            // ErrorExceptions wrap the php-error types within the 'severity' property
            $code = Misc::translateErrorCode($exception->getSeverity());
        }

        return (string) $code;
    }

    /**
     * @return string
     */
    public function contentType()
    {
        return 'text/html';
    }

    /**
     * Adds an entry to the list of tables displayed in the template.
     *
     * The expected data is a simple associative array. Any nested arrays
     * will be flattened with `print_r`.
     *
     * @param string $label
     * @param array  $data
     *
     * @return void
     */
    public function addDataTable($label, array $data)
    {
        $this->extraTables[$label] = $data;
    }

    /**
     * Lazily adds an entry to the list of tables displayed in the table.
     *
     * The supplied callback argument will be called when the error is
     * rendered, it should produce a simple associative array. Any nested
     * arrays will be flattened with `print_r`.
     *
     * @param string   $label
     * @param callable $callback Callable returning an associative array
     *
     * @throws InvalidArgumentException If $callback is not callable
     *
     * @return void
     */
    public function addDataTableCallback($label, /* callable */ $callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Expecting callback argument to be callable');
        }

        $this->extraTables[$label] = function (\Whoops\Exception\Inspector $inspector = null) use ($callback) {
            try {
                $result = call_user_func($callback, $inspector);

                // Only return the result if it can be iterated over by foreach().
                return is_array($result) || $result instanceof \Traversable ? $result : [];
            } catch (\Exception $e) {
                // Don't allow failure to break the rendering of the original exception.
                return [];
            }
        };
    }

    /**
     * Returns all the extra data tables registered with this handler.
     *
     * Optionally accepts a 'label' parameter, to only return the data table
     * under that label.
     *
     * @param string|null $label
     *
     * @return array[]|callable
     */
    public function getDataTables($label = null)
    {
        if ($label !== null) {
            return isset($this->extraTables[$label]) ?
            $this->extraTables[$label] : [];
        }

        return $this->extraTables;
    }

    /**
     * Set whether to handle unconditionally.
     *
     * Allows to disable all attempts to dynamically decide whether to handle
     * or return prematurely. Set this to ensure that the handler will perform,
     * no matter what.
     *
     * @param bool|null $value
     *
     * @return bool|null
     */
    public function handleUnconditionally($value = null)
    {
        if (func_num_args() == 0) {
            return $this->handleUnconditionally;
        }

        $this->handleUnconditionally = (bool) $value;
    }

    /**
     * Adds an editor resolver.
     *
     * Either a string, or a closure that resolves a string, that can be used
     * to open a given file in an editor. If the string contains the special
     * substrings %file or %line, they will be replaced with the correct data.
     *
     * @example
     *  $run->addEditor('macvim', "mvim://open?url=file://%file&line=%line")
     * @example
     *   $run->addEditor('remove-it', function($file, $line) {
     *       unlink($file);
     *       return "http://stackoverflow.com";
     *   });
     *
     * @param string          $identifier
     * @param string|callable $resolver
     *
     * @return void
     */
    public function addEditor($identifier, $resolver)
    {
        $this->editors[$identifier] = $resolver;
    }

    /**
     * Set the editor to use to open referenced files.
     *
     * Pass either the name of a configured editor, or a closure that directly
     * resolves an editor string.
     *
     * @example
     *   $run->setEditor(function($file, $line) { return "file:///{$file}"; });
     * @example
     *   $run->setEditor('sublime');
     *
     * @param string|callable $editor
     *
     * @throws InvalidArgumentException If invalid argument identifier provided
     *
     * @return void
     */
    public function setEditor($editor)
    {
        if (!is_callable($editor) && !isset($this->editors[$editor])) {
            throw new InvalidArgumentException(
                "Unknown editor identifier: $editor. Known editors:" .
                implode(",", array_keys($this->editors))
            );
        }

        $this->editor = $editor;
    }

    /**
     * Get the editor href for a given file and line, if available.
     *
     * @param string $filePath
     * @param int    $line
     *
     * @throws InvalidArgumentException If editor resolver does not return a string
     *
     * @return string|bool
     */
    public function getEditorHref($filePath, $line)
    {
        $editor = $this->getEditor($filePath, $line);

        if (empty($editor)) {
            return false;
        }

        // Check that the editor is a string, and replace the
        // %line and %file placeholders:
        if (!isset($editor['url']) || !is_string($editor['url'])) {
            throw new UnexpectedValueException(
                __METHOD__ . " should always resolve to a string or a valid editor array; got something else instead."
            );
        }

        $editor['url'] = str_replace("%line", rawurlencode($line), $editor['url']);
        $editor['url'] = str_replace("%file", rawurlencode($filePath), $editor['url']);

        return $editor['url'];
    }

    /**
     * Determine if the editor link should act as an Ajax request.
     *
     * @param string $filePath
     * @param int    $line
     *
     * @throws UnexpectedValueException If editor resolver does not return a boolean
     *
     * @return bool
     */
    public function getEditorAjax($filePath, $line)
    {
        $editor = $this->getEditor($filePath, $line);

        // Check that the ajax is a bool
        if (!isset($editor['ajax']) || !is_bool($editor['ajax'])) {
            throw new UnexpectedValueException(
                __METHOD__ . " should always resolve to a bool; got something else instead."
            );
        }
        return $editor['ajax'];
    }

    /**
     * Determines both the editor and if ajax should be used.
     *
     * @param string $filePath
     * @param int    $line
     *
     * @return array
     */
    protected function getEditor($filePath, $line)
    {
        if (!$this->editor || (!is_string($this->editor) && !is_callable($this->editor))) {
            return [];
        }

        if (is_string($this->editor) && isset($this->editors[$this->editor]) && !is_callable($this->editors[$this->editor])) {
            return [
                'ajax' => false,
                'url'  => $this->editors[$this->editor],
            ];
        }

        if (is_callable($this->editor) || (isset($this->editors[$this->editor]) && is_callable($this->editors[$this->editor]))) {
            if (is_callable($this->editor)) {
                $callback = call_user_func($this->editor, $filePath, $line);
            } else {
                $callback = call_user_func($this->editors[$this->editor], $filePath, $line);
            }

            if (empty($callback)) {
                return [];
            }

            if (is_string($callback)) {
                return [
                    'ajax' => false,
                    'url'  => $callback,
                ];
            }

            return [
                'ajax' => isset($callback['ajax']) ? $callback['ajax'] : false,
                'url'  => isset($callback['url']) ? $callback['url'] : $callback,
            ];
        }

        return [];
    }

    /**
     * Set the page title.
     *
     * @param string $title
     *
     * @return void
     */
    public function setPageTitle($title)
    {
        $this->pageTitle = (string) $title;
    }

    /**
     * Get the page title.
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * Adds a path to the list of paths to be searched for resources.
     *
     * @param string $path
     *
     * @throws InvalidArgumentException If $path is not a valid directory
     *
     * @return void
     */
    public function addResourcePath($path)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(
                "'$path' is not a valid directory"
            );
        }

        array_unshift($this->searchPaths, $path);
    }

    /**
     * @return array
     */
    public function getResourcePaths()
    {
        return $this->searchPaths;
    }

    /**
     * Finds a resource, by its relative path, in all available search paths.
     *
     * The search is performed starting at the last search path, and all the
     * way back to the first, enabling a cascading-type system of overrides for
     * all resources.
     *
     * @param string $resource
     *
     * @throws RuntimeException If resource cannot be found in any of the available paths
     *
     * @return string
     */
    protected function getResource($resource)
    {
        if (isset($this->resourceCache[$resource])) {
            return $this->resourceCache[$resource];
        }

        foreach ($this->searchPaths as $path) {
            $fullPath = $path . "/$resource";

            if (is_file($fullPath)) {
                $this->resourceCache[$resource] = $fullPath;
                return $fullPath;
            }
        }

        throw new RuntimeException(
            "Could not find resource '$resource' in any resource paths."
            . "(searched: " . join(", ", $this->searchPaths) . ")"
        );
    }

    /**
     * @deprecated
     *
     * @return string
     */
    public function getResourcesPath()
    {
        $allPaths = $this->getResourcePaths();

        return end($allPaths) ?: null;
    }

    /**
     * @deprecated
     *
     * @param string $resourcesPath
     *
     * @return void
     */
    public function setResourcesPath($resourcesPath)
    {
        $this->addResourcePath($resourcesPath);
    }

    /**
     * Return the application paths.
     *
     * @return array
     */
    public function getApplicationPaths()
    {
        return $this->applicationPaths;
    }

    /**
     * Set the application paths.
     *
     * @param array $applicationPaths
     *
     * @return void
     */
    public function setApplicationPaths($applicationPaths)
    {
        $this->applicationPaths = $applicationPaths;
    }

    /**
     * blacklist a sensitive value within one of the superglobal arrays.
     * Alias for the hideSuperglobalKey method.
     *
     * @param string $superGlobalName
     * @param string $key
     * @see hideSuperglobalKey
     *
     * @return void
     */
    public function blacklist($superGlobalName, $key)
    {
        $this->blacklist[$superGlobalName][] = $key;
    }

    /**
     * Checks all values within the given superGlobal array.
     *
     * Blacklisted values will be replaced by a equal length string cointaining
     * only '*' characters. We intentionally dont rely on $GLOBALS as it
     * depends on the 'auto_globals_jit' php.ini setting.
     *
     * @param array  $superGlobal     One of the superglobal arrays
     * @param string $superGlobalName The name of the superglobal array, e.g. '_GET'
     *
     * @return array $values without sensitive data
     */
    private function masked(array $superGlobal, $superGlobalName)
    {
        $blacklisted = $this->blacklist[$superGlobalName];

        $values = $superGlobal;

        foreach ($blacklisted as $key) {
            if (isset($superGlobal[$key]) && is_string($superGlobal[$key])) {
                $values[$key] = str_repeat('*', strlen($superGlobal[$key]));
            }
        }

        return $values;
    }
}