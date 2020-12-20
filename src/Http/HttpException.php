<?php

namespace Impack\WP\Http;

use Exception;
use Impack\WP\Exceptions\JsonRender;

class HttpException extends Exception
{
    use JsonRender;
}