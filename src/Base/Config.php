<?php

namespace Impack\WP\Base;

use Impack\Config\Config as ImpackConfig;
use Impack\WP\Base\Application;
use Impack\WP\Base\OptionLoader;

class Config extends ImpackConfig
{
    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->loader('option', new OptionLoader);
    }
}