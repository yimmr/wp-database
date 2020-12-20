<?php
namespace Impack\WP;

use Impack\Config\Config as ImpackConfig;
use Impack\WP\Application;
use Impack\WP\OptionLoader;

class Config extends ImpackConfig
{
    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->loader('option', new OptionLoader);
    }
}