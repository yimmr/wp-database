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

        return trim($string);
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

    /**
     * 返回上传图片的字段
     *
     * @param string $name
     * @param int $value
     * @param string $size  单位px,val=宽度:高占比
     * @param string $class
     * @param array $attr
     * @return string
     */
    public static function image($name, $value = null, $size = '100', $class = '', $attr = [])
    {
        $size = explode(':', $size);

        $attr['style'] = ($attr['style'] ?? '') . ";width:$size[0]px;height:" . (isset($size[1]) ? $size[0] * $size[1] : $size[0]) . 'px';

        $html = sprintf('<span class="image-field %s" %s>', $class, static::getAttr($attr));

        if ($imageUrl = \wp_get_attachment_image_url($value, 'full')) {
            $html .= sprintf('<img src="%s">', $imageUrl);
        }

        $html .= '<span class="cancel" onclick="ImwpImageField.cancel(this)"></span>';
        $html .= '<span class="tips" onclick="ImwpImageField.upload(this)">上传</span>';
        $html .= static::input($name, $value, 'hidden');
        $html .= '</span>';

        return $html;
    }

    /**
     * 引入所有字段的脚本和样式
     */
    public static function enqueue()
    {
        \wp_enqueue_media();
        \wp_add_inline_style('mediaelement', static::script('css'));
        \wp_add_inline_script('mediaelement', static::script('js'));
    }

    /**
     * 返回字段的JS或CSS代码
     *
     * @param string $type
     * @return string
     */
    public static function script($type = 'js')
    {
        return file_get_contents(__DIR__ . '/assets/form.' . $type);
    }
}