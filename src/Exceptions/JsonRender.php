<?php
namespace Impack\WP\Exceptions;

use Impack\WP\Exceptions\ErrEnum;
use Throwable;

trait JsonRender
{
    public function __construct($message = '', $code = 0, ?Throwable $previous = null)
    {
        $error = func_num_args() > 1 ? func_get_args() : array_reverse($this->parseErrorInfo($message));

        parent::__construct(...$error);
    }

    /**
     * 返回JSON格式的信息
     *
     * @return string
     */
    public function render()
    {
        return json_encode([
            'message' => $this->getMessage(),
            'code'    => $this->getCode(),
            'data'    => '',
        ]);
    }

    /**
     * 解析异常信息
     *
     * @param  mixed  $key
     * @return array
     */
    protected function parseErrorInfo($key)
    {
        if (\is_wp_error($key)) {
            return [$key->get_error_code(), $key->get_error_message()];
        }

        // 若存在自定义枚举，则返回自定义枚举值
        if (property_exists($this, 'enum') && is_callable($this->enum, $key)) {
            return $this->enum::$key()->getValue();
        }

        if (ErrEnum::exist($key)) {
            return ErrEnum::$key()->getValue();
        }

        return ErrEnum::UNKNOWN_ERR;
    }
}