<?php

namespace Impack\WP\Http;

use BadMethodCallException;
use Impack\WP\Http\HttpException;

if (!function_exists('wp_validate_redirect')) {
    require_once rtrim(\ABSPATH, '\/') . '/wp-includes/pluggable.php';
}

if (!function_exists('wp_remote_request')) {
    require_once rtrim(\ABSPATH, '\/') . '/wp-includes/http.php';
}

/**
 * @method static static request($url, $params = [])
 * @method static static get($url, $params = [])
 * @method static static post($url, $params = [])
 * @method static static head($url, $params = [])
 */
class Remote
{
    protected $response;

    protected $method;

    public function __construct($method = 'get')
    {
        $this->method = $method;
    }

    /**
     * 返回响应头信息
     *
     * @param  string|null  $header
     * @return array|string
     */
    public function header($header = null)
    {
        if (is_null($header)) {
            return $this->response['headers'] ?? [];
        }

        return $this->response['headers'][$header] ?? '';
    }

    /**
     * 返回响应主体
     *
     * @return string
     */
    public function body()
    {
        return $this->response['body'] ?? '';
    }

    /**
     * 响应消息
     *
     * @return string
     */
    public function message()
    {
        return $this->response['response']['message'] ?? '';
    }

    /**
     * 状态码
     *
     * @return int
     */
    public function code()
    {
        return $this->response['response']['code'] ?? 0;
    }

    /**
     * 下载的文件名
     *
     * @return string
     */
    public function fileName()
    {
        return $this->response['filename'] ?: '';
    }

    /**
     * 返回cookie
     *
     * @param string|null $name
     * @return array|string
     */
    public function cookie($name = null)
    {
        if (is_null($name)) {
            return $this->response['cookies'] ?? [];
        }

        foreach ((array) $this->response['cookies'] as $cookie) {
            if ($cookie->name === $name) {
                return $cookie->value;
            }
        }

        return '';
    }

    /**
     * 请求的URL
     *
     * @return string
     */
    public function url()
    {
        return $this->reponseObject()->url;
    }

    /**
     * 是否请求成功
     *
     * @return bool
     */
    public function success()
    {
        return $this->reponseObject()->success;
    }

    /**
     * 本次请求链
     *
     * @return array
     */
    public function history()
    {
        return $this->reponseObject()->history;
    }

    /**
     * 本次请求方法
     *
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * 响应对象
     *
     * @return object
     */
    protected function reponseObject()
    {
        return $this->response['http_response']->get_response_object();
    }

    /**
     * 发送HTTP请求
     *
     * @param  string  $url
     * @param  array  $params
     * @return $this
     *
     * @throws \Impack\WP\Http\HttpException
     */
    protected function remote($url, $params = [])
    {
        $this->response = ("wp_safe_remote_{$this->method}")($url, $params);

        if (\is_wp_error($this->response)) {
            throw new HttpException($this->response);
        }

        return $this;
    }

    /**
     * 处理静态调用
     *
     * @param  string  $method
     * @param  array  $arguments
     * @return static
     */
    public static function __callStatic($method, $arguments)
    {
        if (in_array($method, ['get', 'post', 'head', 'request'])) {
            return (new static($method))->remote(...$arguments);
        }

        throw new BadMethodCallException("不存在方法：$method");
    }
}