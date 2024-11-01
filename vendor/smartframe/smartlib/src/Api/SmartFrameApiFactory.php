<?php

namespace SmartFrameLib\Api;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Config\Config;

class SmartFrameApiFactory
{
    private static $instance = null;

    public static function create($cache = true)
    {
        if (self::$instance === null || !$cache) {
            $optionProvider = SmartFrameOptionProviderFactory::create();
            $host = preg_replace('/[^a-zA-Z0-9\']/', '_', $_SERVER['HTTP_HOST']);
            $prefix = 'wordpress-' . $host . '-';
            self::$instance = new LazyLoadingSmartFrameApi (new SmartFrameApi(Config::instance()->getConfig(SMARTFRAME_API_ENDPOINT), $optionProvider->getApiKey()), $prefix);
        }

        return self::$instance;
    }
}
