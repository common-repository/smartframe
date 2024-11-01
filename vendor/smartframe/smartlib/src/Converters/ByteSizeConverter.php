<?php

namespace SmartFrameLib\Converters;
 if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class FileSizeConverter
 * @package Utils
 */
class ByteSizeConverter
{
    /**
     * 500000 => 488.3K
     * @param $bytes
     * @param int $precision
     * @return string
     */
    public static function bytesToShortFormat($bytes, $precision = 1)
    {
        if ($bytes < 900) {
            // 0 - 900
            $n_format = number_format($bytes, $precision);
            $suffix = 'B';
        } else if ($bytes < 900000) {
            // 0.9k-850k
            $n_format = number_format($bytes / 1024, $precision);
            $suffix = 'KB';
        } else if ($bytes < 900000000) {
            // 0.9m-850m
            $n_format = number_format($bytes / pow(1024, 2), $precision);
            $suffix = 'MB';
        } else if ($bytes < 900000000000) {
            // 0.9g-850g
            $n_format = number_format($bytes / pow(1024, 3), $precision);
            $suffix = 'GB';
        } else {
            // 0.9t+
            $n_format = number_format($bytes / pow(1024, 4), $precision);
            $suffix = 'TB';
        }
        // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
        // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if ($precision > 0) {
            $dotzero = '.' . str_repeat('0', $precision);
            $n_format = str_replace($dotzero, '', $n_format);
        }
        return $n_format . $suffix;
    }

    /**
     * 1K => 1024
     * @param $shortFormat
     * @return int
     */
    public static function shortFormatToBytes($shortFormat)
    {
        $number = substr($shortFormat, 0, -1);
        switch (strtoupper(substr($shortFormat, -1))) {
            case 'K':
                $result = $number * 1024;
                break;
            case 'M':
                $result = $number * pow(1024, 2);
                break;
            case 'G':
                $result = $number * pow(1024, 3);
                break;
            case 'T':
                $result = $number * pow(1024, 4);
                break;
            case 'P':
                $result = $number * pow(1024, 5);
                break;
            default:
                $result = (int)$shortFormat;
        }
        return (int)round($result);
    }
}