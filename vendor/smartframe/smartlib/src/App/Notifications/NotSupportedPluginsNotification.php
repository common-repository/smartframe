<?php

namespace SmartFrameLib\App\Notifications;

use SmartFrameLib\App\Settings\MetaBoxes\Ajax\ApiAjaxWrapper;
use SmartFrameLib\View\ViewRenderFactory;

if (!defined('ABSPATH')) exit;

class NotSupportedPluginsNotification
{

    const NOT_COMPATIBLE_NAMES = ['smush', 'wpbakery', 'foogallery', 'modula'];
    const NOT_COMPATIBLE_URLS = [
        '10web.io\/plugins\/wordpress-photo-gallery',
        'robosoft.co\/wordpress-gallery-plugin',
        'enviragallery.com',
        'imagely.com\/wordpress-gallery-plugin\/nextgen-gallery',
        'revolution.themepunch.com',
        'elegantthemes.com',
        'instapage.com',
        'processby\.com\/lazy-load-wordpress\/',
        'responsive-images-and-lazy-loading-in-wordpress',
        'wordpress\.org\/plugins\/lazysizes\/',
        'shins-pageload-magic',
        'smart-image-loader',
        'jetpack\.com',
        'wpbeaverbuilder\.com',
        'wordpress\.org\/plugins\/rocket\-lazy\-load\/'

    ];

    public function checkUnsupportedPlugin($pluginName)
    {
        $plugins = get_plugins();
        $amountOfUnsupportedPlugins = count($this->filterUnsupportedPlugins([$plugins[$pluginName]]));
        if ($amountOfUnsupportedPlugins !== 0) {
            delete_option(ApiAjaxWrapper::UNSUPPORTED_PLUGIN_INFO);
        }
    }

    public function showNotification()
    {
        $activated_plugins = $this->activatedPlugins();

        $unsupportedPlugins = array_map(function ($row) {
            return $row['Name'];
        }, $this->filterUnsupportedPlugins($activated_plugins));

        if (!empty($unsupportedPlugins) && get_option(ApiAjaxWrapper::UNSUPPORTED_PLUGIN_INFO) === false) {
            echo ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/notifications/plugin-incopatibility.php', ['list' => $unsupportedPlugins]);
        }
    }

    /**
     * @param array $activated_plugins
     * @param array $notCompatiblePluginsNames
     * @param array $notCompatiblePluginsUrls
     * @return array
     */
    private function filterUnsupportedPlugins(array $activated_plugins)
    {
        $unsuportedPlugins = array_filter($activated_plugins, function ($row) {
            if (preg_match('/' . implode('|', self::NOT_COMPATIBLE_NAMES) . '/', strtolower($row['Name']))) {
                return true;
            }
            if (preg_match('/' . implode('|', self::NOT_COMPATIBLE_URLS) . '/', strtolower($row['PluginURI']))) {
                return true;
            }

            return false;
        });
        return $unsuportedPlugins;
    }

    /**
     * @return array
     */
    private function activatedPlugins()
    {
        $apl = get_option('active_plugins');
        $plugins = get_plugins();
        $activated_plugins = [];
        foreach ($apl as $p) {
            if (isset($plugins[$p])) {
                array_push($activated_plugins, $plugins[$p]);
            }
        }
        return $activated_plugins;
    }
}