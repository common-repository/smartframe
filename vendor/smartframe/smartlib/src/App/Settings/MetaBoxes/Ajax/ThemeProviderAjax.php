<?php namespace SmartFrameLib\App\Settings\MetaBoxes\Ajax;

use SmartFrameLib\App\Theme\ThemeProvider;

if (!defined('ABSPATH')) exit;

class ThemeProviderAjax
{

    /**
     * @var ThemeProvider
     */
    private $themeProvider;

    public function __construct()
    {
        $this->themeProvider = ThemeProvider::create();
    }

    public function provideAvailableThemes()
    {
        wp_send_json( $this->themeProvider->provideKeyValueTheme());
    }

}