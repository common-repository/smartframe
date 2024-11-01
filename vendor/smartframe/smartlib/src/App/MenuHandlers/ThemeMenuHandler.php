<?php

namespace SmartFrameLib\App\MenuHandlers;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\App\Settings\Handlers\ThemeSettingsHandler;
use SmartFrameLib\App\SmartFramePlugin;
use SmartFrameLib\App\Theme\ThemeProvider;

class ThemeMenuHandler
{

    const MENU_SLUG = 'smart-frame-examples';

    /**
     * DashboardMenuHandler constructor.
     */
    public function __construct()
    {
        //prepare settings fields to dispaly
    }

    /**
     * @return string
     */
    public function display()
    {
        return \SmartFrameLib\View\ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/settings/themes.php', [
        ])->display();
    }

    public static function menuLinkProvider()
    {
        return admin_url('admin.php?page=' . ThemeMenuHandler::MENU_SLUG);
    }
}