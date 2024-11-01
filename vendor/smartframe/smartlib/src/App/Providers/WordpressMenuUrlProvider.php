<?php

namespace SmartFrameLib\App\Providers;

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\App\MenuHandlers\RegisterMenuHandler;

class WordpressMenuUrlProvider
{

    public static function manageThemesUrl()
    {
        $url = "https://panel.smartframe.cloud/theme/manage?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=" . $_SERVER['HTTP_HOST'] . "&utm_content=Manage%20themes";
        if (!SmartFrameApiFactory::create()->get_profile()->isActive()) {
            $url = RegisterMenuHandler::menuLinkProvider() . '&srcr=no-active';
        }

        return $url;
    }


    public static function registerUrl()
    {
        return RegisterMenuHandler::menuLinkProvider();
    }

}