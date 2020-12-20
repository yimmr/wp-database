<?php
namespace Impack\WP;

use Impack\Container\Container;
use Impack\Contracts\Foundation\Application as ApplicationContract;

class Application extends Container implements ApplicationContract
{
    protected $path;

    protected $url;

    protected $hasBootstrapped = false;

    public function __construct($path = null)
    {
        $this->setPath($path);
        $this->registerBaseBindings();
        $this->registerCoreAliases();
    }

    /**
     * 注册基础类到容器
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(Container::class, $this);

        $this->singleton('config', \Impack\WP\Config::class);
    }

    /**
     * 设置应用运行目录
     *
     * @param  string  $basePath
     * @return $this
     */
    public function setPath(string $path)
    {
        if ($path) {
            $this->path = rtrim($path, '\/');
        }

        return $this;
    }

    /**
     * 返回应用运行目录
     *
     * @param  string  $path
     * @return string
     */
    public function path($path = '')
    {
        return $this->path . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * 返回核心类文件目录
     *
     * @param  string  $path
     * @return string
     */
    public function appPath($path = '')
    {
        return $this->path . DIRECTORY_SEPARATOR . 'app' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * 返回配置文件目录
     *
     * @param  string  $path
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->path . DIRECTORY_SEPARATOR . 'config' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * 返回公共资源目录
     *
     * @param  string  $path
     * @return string
     */
    public function publicPath($path = '')
    {
        return $this->path . DIRECTORY_SEPARATOR . 'public' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * 设置运行目录的网址
     *
     * @param  string  $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        if ($url) {
            $this->url = rtrim($url, '/');
        }

        return $this;
    }

    /**
     * 返回运行目录的链接
     *
     * @param  string  $uri
     * @return string
     */
    public function url($uri = '')
    {
        return $this->url . ($uri ? "/$uri" : $uri);
    }

    /**
     * 返回公共资源目录的链接
     *
     * @param  string  $uri
     * @return string
     */
    public function publicUrl($uri = '')
    {
        return $this->url . '/public' . ($uri ? "/$uri" : $uri);
    }

    /**
     * 是否已启动引导程序
     *
     * @return bool
     */
    public function hasBootstrapped()
    {
        return $this->hasBootstrapped;
    }

    /**
     * 启动全局引导程序
     *
     * @param  array  $bootstrappers
     */
    public function bootstrap(array $bootstrappers)
    {
        $this->hasBootstrapped = true;
        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    /**
     * 是否在调试模式下运行
     *
     * @return bool
     */
    public function isDebugging()
    {
        return $this['config']['app.debug'] ?? \WP_DEBUG === true;
    }

    /**
     * 注册核心类的别名
     */
    protected function registerCoreAliases()
    {
        foreach ([
            'app'        => [self::class, ApplicationContract::class, \Impack\Contracts\Container\Container::class],
            'config'     => [\Impack\WP\Config::class, \Impack\Contracts\Config\Repository::class],
            'filesystem' => [\Impack\WP\Support\Filesystem::class],
            'remote'     => [\Impack\WP\Http\Remote::class],
        ] as $id => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($id, $alias);
            }
        }
    }
}