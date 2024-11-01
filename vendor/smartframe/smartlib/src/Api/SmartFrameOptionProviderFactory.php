<?php

namespace SmartFrameLib\Api;
 if ( ! defined( 'ABSPATH' ) ) exit;

use SmartFrameLib\App\SmartFramePlugin;

class SmartFrameOptionProviderFactory
{
    private static $instance;

    public static function create()
    {
        if (self::$instance === null) {
            self::$instance = new SmartFrameOptionProvider(SmartFramePlugin::provideOptionPrefix());
        }
        return self::$instance;
    }
}