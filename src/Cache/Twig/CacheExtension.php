<?php

namespace Athorrent\Cache\Twig;

use Athorrent\Cache\Twig\TokenParser\Cache;
use Phpfastcache\Bundle\Twig\CacheExtension\Extension;

class CacheExtension extends Extension
{
    public function getName()
    {
        if (\version_compare(\Twig_Environment::VERSION, '1.26.0', '>=')) {

            return Extension::class;
        }

        return parent::getName();
    }

    public function getTokenParsers()
    {
        return [
            new Cache()
        ];
    }
}