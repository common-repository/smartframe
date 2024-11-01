<?php

namespace SmartFrameLib\App;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\MenuHandlers\PropertiesMenuHandler;
use SmartFrameLib\App\Migrations\MigrationLoader;
use SmartFrameLib\App\RestActions\RestRouteLoader;
use SmartFrameLib\App\Sections\Admin\AdminSectionManager;
use SmartFrameLib\App\Sections\Publicc\PublicSectionManager;
use SmartFrameLib\App\Statistics\StatisticsCollector;
use SmartFrameLib\Config\Config;

class SmartFramePlugin
{

    public static $PLUGIN_NAME = 'sfm-smartframe';
    public static $PLUGIN_OPTION_PREFIX = 'sfm_smartframe';
    public static $VERSION = '2.2';

    private $adminSection;
    private $publicSection;

    public function __construct()
    {
        require_once 'config.plugin.php';

        $this->adminSection = new AdminSectionManager();
        $this->publicSection = new PublicSectionManager();
        $this->restLoader = new RestRouteLoader();
    }

    public static function provideVersion()
    {
        return self::$VERSION;
    }

    /**
     * @return string
     */
    public static function provideName()
    {
        return self::$PLUGIN_NAME;
    }

    /**
     * @return string
     */
    public static function provideOptionPrefix()
    {
        return self::$PLUGIN_OPTION_PREFIX;
    }

    public static function create()
    {
        return new self();
    }

    public function run()
    {
        global $pagenow;

        //register activation hook when we are on plugins page
        if ($pagenow === 'plugins.php') {
            add_action('admin_notices', [$this, 'fx_admin_notice_example_notice']);
            register_activation_hook(SMARTFRAME_PLUGIN_BASE_NAME, [$this, 'activate']);
            register_deactivation_hook(SMARTFRAME_PLUGIN_BASE_NAME, [$this, 'deactivate']);
        }

        //Load only needed hooks for admin section or public section
        if (is_admin() || preg_match('/wp-json\/wp\//', $_SERVER['REQUEST_URI']) === 1) {
            (new  \SmartFrameLib\App\Migrations\MigrationLoader())->load();
            $this->adminSection->loadHooks();
            $api = SmartFrameApiFactory::create();
            $profile = $api->get_profile();
            if (empty($profile->getExternalImageSource())) {
                $api->setExternalImageSource(['externalImageSource' => 'wordpress-plugin-image-source', 'wpPluginApiUrl' => Config::instance()->getConfig('wpPluginApiUrl')]);
                $account = $api->get_profile(true);
                SmartFrameOptionProviderFactory::create()->setWpPluginApiKey($account->getWpPluginApiKey());
            }
        } else {
            $this->publicSection->loadHooks();
        }

        $this->restLoader->load();
    }

    public function activate()
    {
        (new  \SmartFrameLib\App\Migrations\MigrationLoader())->load();
        SmartFrameOptionProviderFactory::create()->setApiKey('');
        set_transient('fx-admin-notice-example', true, 5);
        SmartFrameOptionProviderFactory::create()->setDefaultSettings();
        add_option('smart_my_plugin_activation', 'just-activated');
        (new MigrationLoader())->load();
    }

    public function deactivate()
    {
        wp_clear_scheduled_hook('on24hours');
        wp_clear_scheduled_hook('on5min');
        delete_option('smartframe_privacy_policy');
        delete_option('smartframe-current-comments-amount');
        delete_option('smartframe-current-post-pages-amount');
        delete_option('smartframe-current-image-count');
        (new MigrationLoader())->removeMigrationVariables();
        if (!empty(SmartFrameOptionProviderFactory::create()->getApiKey())) {
            SmartFrameApiFactory::create(false)->setExternalImageSource(['externalImageSource' => '']);
        }
        SmartFrameOptionProviderFactory::create()->setApiKeyWasValidOnSave(false);
        (new StatisticsCollector())->sendPluginDeactivationTime((new \DateTime())->format('Y-m-d H:i:s'));
        (new StatisticsCollector())->sendPluginStatus('Not active');
        SmartFrameOptionProviderFactory::create()->setApiKey('');
//        SmartFrameOptionProviderFactory::create()->setThemeForSmartframe('');
    }

    public function fx_admin_notice_example_notice()
    {
        /* Check transient, if available display notice */
        if (get_transient('fx-admin-notice-example')) {
            ?>
            <div class="updated notice is-dismissible">
                <p>
                    You have successfully activated the SmartFrame plugin.
                    <a href="<?php echo PropertiesMenuHandler::menuLinkProvider() ?>">Get started for free with 2GB
                        secure cloud storage </a>
                </p>
            </div>
            <?php
            /* Delete transient, only display this notice once. */
            delete_transient('fx-admin-notice-example');
        }
    }

}