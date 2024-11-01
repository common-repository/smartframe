<?php

namespace SmartFrameLib\Loger;

use SmartFrameLib\Config\Config;

class FileLogger
{
    public static function log($stringToLog, $fileName = 'logs.txt')
    {
        if (Config::instance()->hasKey('DEBUG') && Config::instance()->getConfig('DEBUG') === true) {
            $fileName = SMARTFRAME_PLUGIN_DIR . '/logs/' . $fileName;
            $stream = fopen($fileName, 'a+');
            @fwrite($stream, $stringToLog . "\n");
            fclose($stream);
        }
    }
}