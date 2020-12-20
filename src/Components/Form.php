<?php

namespace Impack\WP\Components;

class Form
{
    /**
     * 数组转成html属性字符串
     *
     * @param  array|string  $attr
     * @return string
     */
    public static function getAttr($attr)
    {
        if (!is_array($attr)) {
            return $attr;
        }

        $string = '';
        foreach ($attr as $key => $value) {
            $string .= "$key=\"$value\" ";
        }

        return trim($attr);
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
    public static function input($name = '', $value = null, $type = 'text', $attr = '')
    {
        $attr = static::getAttr($attr);

        if (!is_null($value)) {
            $attr = "value=\"$value\" " . $attr;
        }

        return "<input type=\"$type\" name=\"$name\" $attr>";
    }

    /**
     * 多行文本域
     *
     * @param  string  $name
     * @param  mixed   $value
     * @param  int     $rows
     * @param  int     $cols
     * @param  string|array  $attr
     * @return string
     */
    public static function textarea($name, $value = null, $rows = 5, $cols = 50, $attr = '')
    {
        $attr = static::getAttr($attr);

        return "<textarea name=\"$name\" rows=\"$rows\" cols=\"$cols\" $attr>$value</textarea>";
    }

    /**
     * 下拉框
     *
     * @param  string  $name
     * @param  mixed   $value
     * @param  array   $options
     * @param  string|array  $attr
     * @return string
     */
    public static function select($name, $value = null, array $options = [], $attr = '')
    {
        $attr = static::getAttr($attr);
        $html = "<select name=\"$name\" $attr>";

        foreach ($options as $key => $option) {
            $html .= sprintf('<option value="%s"%s>%s</option>', $key, ($value == $key ? ' selected' : ''), $option);
        }

        return $html . '</select>';
    }

    /**
     * 复选框
     *
     * @param  string  $name
     * @param  mixed   $checked
     * @param  mixed   $value
     * @param  string  $label
     * @param  string  $id
     * @param  array|string  $attr
     * @return string
     */
    public static function checkbox($name, $checked = false, $value = null, $label = '', $id = null, $attr = '')
    {
        $attr = static::getAttr($attr) . static::getCheckedAttr($checked, $value, $id);
        $attr = static::input($name, $value, 'checkbox', $attr) . $label;

        return $id ? "<label for=\"$id\">" . $attr . '</label>' : $attr;
    }

    /**
     * 单选框
     *
     * @param  string  $name
     * @param  mixed   $checked
     * @param  mixed   $value
     * @param  string  $label
     * @param  string  $id
     * @param  array|string  $attr
     * @return string
     */
    public static function radio($name, $checked = false, $value = null, $label = '', $id = null, $attr = '')
    {
        $attr = static::getAttr($attr) . static::getCheckedAttr($checked, $value, $id);
        $attr = static::input($name, $value, 'radio', $attr) . $label;

        return $id ? "<label for=\"$id\">" . $attr . '</label>' : $attr;
    }

    /**
     * Number类型的字段
     *
     * @param  string  $name
     * @param  int     $value
     * @param  int     $min
     * @param  int     $max
     * @param  string|array  $attr
     * @return string
     */
    public static function number($name, $value = 0, $min = null, $max = null, $attr = '')
    {
        $attr = "min=\"$min\" max=\"$max\" class=\"tiny-text\" " . static::getAttr($attr);

        return static::input($name, $value, 'number', $attr);
    }

    /**
     * 获取字段的checked和id属性
     *
     * @param  mixed  $checked
     * @param  mixed  $value
     * @param  string  $id
     * @return string
     */
    public static function getCheckedAttr($checked, $value = null, $id = null)
    {
        $id = $id ? " id=\"$id\"" : '';

        if (is_null($value)) {
            return $id . $checked ? ' checked' : '';
        }

        return $id . $checked == $value ? ' checked' : '';
    }
}