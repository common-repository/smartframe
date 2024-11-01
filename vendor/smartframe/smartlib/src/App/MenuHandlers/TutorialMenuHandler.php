<?php

namespace SmartFrameLib\App\MenuHandlers;
 if ( ! defined( 'ABSPATH' ) ) exit;

use SmartFrameLib\App\MenuManager\SmartFrameAdminMenuManager;

class TutorialMenuHandler
{

    const MENU_SLUG = SmartFrameAdminMenuManager::ADMIN_MENU_PREFIX . 'tutorial';

    /**
     * DashboardMenuHandler constructor.
     */
    public function __construct()
    {
        //to implement se
    }

    /**
     * @return string
     */
    public function display()
    {
        return \SmartFrameLib\View\ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/settings/tutorial.php', [])
            ->display();
    }

    public static function menuLinkProvider()
    {
        return admin_url('admin.php?page=' . self::MENU_SLUG);
    }

}