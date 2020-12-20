<?php
namespace Impack\WP\Exceptions;

use Impack\Contracts\Foundation\Application;
use Impack\WP\Contracts\ExceptionHandler;
use Throwable;

class Handler implements ExceptionHandler
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 记录异常
     *
     * @param  Throwable  $e
     */
    public function report(Throwable $e)
    {
        if (method_exists($e, 'report')) {
            call_user_func([$e, 'report']);
        }
    }

    /**
     * 输出异常信息
     *
     * @param  Throwable  $e
     */
    public function render(Throwable $e)
    {
        if (method_exists($e, 'render')) {
            echo call_user_func([$e, 'render']);
            return;
        }

        // 非调试模式下不输出简单错误
        if (property_exists($e, 'isSimple') && !$this->app->isDebugging()) {
            return;
        }

        if ($this->isJsonRequest()) {
            $data = $this->prepareJsonResponse($e);
        } else {
            $data = $this->prepareResponse($e);
        }

        echo $data;
    }

    /**
     * 是否是JSON请求
     *
     * @return bool
     */
    protected function isJsonRequest()
    {
        if (isset($_SERVER['HTTP_ACCEPT']) && false !== strpos($_SERVER['HTTP_ACCEPT'], 'application/json')) {
            return true;
        }

        if (isset($_SERVER['CONTENT_TYPE']) && 'application/json' === $_SERVER['CONTENT_TYPE']) {
            return true;
        }

        return false;
    }

    /**
     * 返回异常页面
     *
     * @param  \Throwable  $e
     * @return string
     */
    protected function prepareResponse(Throwable $e)
    {
        if ($this->app->isDebugging()) {
            return $this->renderExceptionWithWhoops($e);
        }

        \wp_die('SERVER ERROR');
    }

    /**
     * 返回Json格式的异常信息
     *
     * @param \Throwable $e
     * @return string
     */
    protected function prepareJsonResponse(Throwable $e)
    {
        if (!$this->app->isDebugging()) {
            return $this->getJsonFormat('SERVER ERROR', '500');
        }

        return $this->getJsonFormat($e->getMessage(), $e->getCode(), $e->getFile() . ':' . $e->getLine());
    }

    /**
     * 返回JSON格式的响应信息
     *
     * @param  string  $message
     * @param  int  $code
     * @param  mixed  $data
     * @return string
     */
    protected function getJsonFormat($message = '', $code = 0, $data = '')
    {
        return json_encode(compact(['code', 'message', 'data']));
    }

    /**
     * 返回Whoops的错误页面
     *
     * @param  \Throwable  $e
     * @return string
     */
    protected function renderExceptionWithWhoops(Throwable $e)
    {
        $whoops = new \Whoops\Run;

        $whoops->pushHandler(new \Impack\WP\ErrPage\PageHandler);

        $whoops->writeToOutput(false);

        $whoops->allowQuit(false);

        return $whoops->handleException($e);
    }
}