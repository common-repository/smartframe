<?php
/**
 * Created by PhpStorm.
 * User: pawelcudzilo
 * Date: 15/11/2018
 * Time: 12:12
 */

namespace SmartFrameLib\App\Settings\Handlers;
 if ( ! defined( 'ABSPATH' ) ) exit;


class ThemeSettingsHandler
{
    private $sfm_smartframe = 'sfm_smartframe_theme';

    private $option_name = 'sfm_smartframe';

    public static function create()
    {
        return new self();
    }

}