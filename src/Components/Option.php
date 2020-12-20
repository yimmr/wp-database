<?php

namespace Impack\WP\Components;

use Closure;

class Option
{
    /**
     * 输出Option页的外层结构
     *
     * @param  string  $title     H1标题
     * @param  string  $fromAttr  form属性
     * @param  string  $children  输出from子级的回调
     */
    public static function wrap($title, $fromAttr = null, Closure $children)
    {
        echo '<div class="wrap">';
        echo '<h1>' . $title . '</h1>';
        echo '<form method="post" novalidate="novalidate" ' . $fromAttr . '>';
        echo '<table class="form-table" role="presentation"><tbody>';

        if (is_callable($children)) {
            $children();
        }

        echo '</tbody></table>';
        echo \get_submit_button();
        echo '</form>';
        echo '</div>';
    }

    /**
     * 输出表格组件
     *
     * @param Closure $children
     */
    public static function table(Closure $children = null)
    {
        echo '<table class="form-table" role="presentation"><tbody>';

        if (is_callable($children)) {
            $children();
        }

        echo '</tbody></table>';
    }

    /**
     * 输出Option表格tr组件
     *
     * @param  string  $label
     * @param  string  $content
     * @param  string  $tips
     * @param  string  $labelFor
     */
    public static function tr($label, $content = '', $tips = '', $labelFor = null)
    {
        echo '<tr><th scope="row">' . "\r\n";
        echo ($labelFor ? '<label for="' . $labelFor . '">' : '<label>') . $label . '</label>';
        echo '</th><td>';
        echo $content;
        echo $tips ? '<p class="description">' . $tips . '</p>' : '';
        echo '</td></tr>';
    }

    /**
     * 单行文本域
     *
     * @param  string  $name
     * @param  mixed   $value
     * @param  string  $type
     * @param  string|array  $attr
     * @return string
     */
    public static function input($name, $value = null, $type = 'text', $attr = '')
    {
        if ($type == 'text') {
            $attr = 'class="regular-text" ' . Form::getAttr($attr);
        }

        return Form::input($name, $value, $type, $attr);
    }
}