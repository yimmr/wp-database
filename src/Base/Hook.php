<?php

namespace Impack\WP\Base;

use Impack\Support\Str;
use Impack\WP\Base\Application;
use ReflectionObject;

class Hook
{
    protected $app;

    protected $type = 'action';

    protected $params = [];

    public function __construct(Application $app)
    {
        $this->app = $app;

        $method = "add_{$this->type}";
        $hooks  = (new ReflectionObject($this))->getMethods();

        foreach ($hooks as $hook) {
            if ($hook->name == '__construct') {
                continue;
            }

            if ($hook->name == 'adding') {
                $this->{$hook->name}();
                continue;
            }

            $name = Str::snake($hook->name);

            if (!isset($this->params[$name])) {
                $method($name, [$this, $hook->name]);
            } elseif (is_array($this->params[$name])) {
                $method($name, [$this, $hook->name], ...$this->params[$name]);
            }
        }
    }
}