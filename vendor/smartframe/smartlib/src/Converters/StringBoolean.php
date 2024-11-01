<?php

namespace SmartFrameLib\Converters;
 if ( ! defined( 'ABSPATH' ) ) exit;

use RuntimeException;

/**
 * Class StringBoolean
 * @package SmartFrameLib\Converters
 */
class StringBoolean
{

    /**
     * @var string
     */
    const OPTION_YES = 'yes';

    /**
     * @var string
     */
    const OPTION_NO = 'no';

    /**
     * @var array
     */
    public static $options = [
        self::OPTION_NO,
        self::OPTION_YES,
    ];

    /**
     * @param $bool boolean
     * @return string
     */
    public static function boolToString($bool)
    {
        if (!is_bool($bool)) {
            return self::OPTION_NO;
//            throw new RuntimeException("Can't parse $bool variable to string!");
        }

        return $bool ? self::OPTION_YES : self::OPTION_NO;
    }

    /**
     * @param $string string
     * @return string
     */
    public static function stringToBool($string)
    {
        return $string === self::OPTION_YES;
    }

    public static function validString($value)
    {
        if (in_array($value, self::$options, true)) {
            return $value;
        }
        return self::OPTION_NO;
    }
}