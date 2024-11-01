<?php

namespace SmartFrameLib\App\Sections\Admin;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\MediaLibrary\MediaLibraryManager;
use SmartFrameLib\App\MenuHandlers\PropertiesMenuHandler;
use SmartFrameLib\App\MenuManager\SmartFrameAdminMenuManager;
use SmartFrameLib\App\Providers\WordpressMenuUrlProvider;
use SmartFrameLib\App\Settings\MetaBoxes\Ajax\ApiAjaxWrapper;
use SmartFrameLib\App\Settings\MetaBoxes\Ajax\AttachmentsDetailsLoadSmartframePreview;
use SmartFrameLib\App\Settings\MetaBoxes\EditAttachmentManager;
use SmartFrameLib\App\Theme\ThemeProvider;
use SmartFrameLib\Config\Config;
use SmartFrameLib\App\Notifications\NotSupportedPluginsNotification;
use SmartFrameLib\View\ViewRenderFactory;

class AdminSectionManager
{
    public function loadHooks()
    {
        add_action('wp_ajax_connectNoRegisteredAccount', [new ApiAjaxWrapper(), 'connectNoRegisteredAccount']);
        add_action('wp_ajax_checkValidAccessCode', [new ApiAjaxWrapper(), 'checkValidAccessCode']);
        add_action('wp_ajax_loadSmartFrameByPostId', [new AttachmentsDetailsLoadSmartframePreview(), 'loadSmartFrame']);
        add_action('wp_ajax_noSupportedPluginList', [new ApiAjaxWrapper(), 'notSupportedPluginsNotificationStatus']);
        add_action('wp_ajax_checkPrivacyPolicy', [new ApiAjaxWrapper(), 'checkPrivacyPolicy']);
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if (SmartFrameApiFactory::create()->check_credentials()) {
            $this->loadHooksWithValidApiKey();
        } else {
            add_filter('attachment_fields_to_edit', function ($fromFields, $post = null) {
                if (0 !== preg_match("/" . 'jpeg|png|bmp|gif' . "/", $post->post_mime_type) && preg_match('/wp-admin\/admin-ajax\.php|wp-admin\/async-upload.php/', $_SERVER['REQUEST_URI']) === 1) {
                    $row = "\t\t</br><p> <b>SMARTFRAME</b></p>";
                    $fromFields['smartframe-settings-header-12'] = [
                        'tr' => $row,
                        'show_in_edit' => false,
                        'application' => 'image',
                        'input' => false,
                        'option_name' => false,
                    ];
                    $fromFields['smartframe-settings-header-2'] = [
                        'tr' => "<p>Configure how to secure and present this image with SmartFrame. Please note that SmartFrame supports JPEG only.</p>
                            <p><a href=" . PropertiesMenuHandler::menuLinkProvider() . ">You're just one step away from activating the SmartFrame plugin</a></p>",
                        'show_in_edit' => false,
                        'application' => 'image',
                        'input' => false,
                        'option_name' => false,
                    ];
                }

                return $fromFields;
            }, 10, 2);

            if (!SmartFrameAdminMenuManager::create()->isOnMenuPage()) {
                add_action('admin_notices', [$this, 'adminNotificationActivateApiKey']);
            }
        }

        if (!SmartFrameAdminMenuManager::create()->isOnMenuPage() && SmartFrameApiFactory::create()->check_credentials() && (get_option('smartframe_privacy_policy') === false)) {
            add_action('admin_notices', [$this, 'adminNotificationActivateApiKey']);
        }
        add_action('admin_menu', [SmartFrameAdminMenuManager::create(), 'loadMenu']);
        add_action('admin_enqueue_scripts', [$this, 'loadScripts']);
    }

    public function loadScripts()
    {
        /** @var string $scriptVersion */
        $scriptVersion = Config::instance()->getConfig('scripts-version');
        wp_enqueue_script('popperr', SMARTFRAME_PLUGIN_URL . 'admin/vendor/js/tippy/popper.min.js', [], false, true);
        wp_enqueue_script('tippy', SMARTFRAME_PLUGIN_URL . 'admin/vendor/js/tippy/index.all.min.js', [], false, true);

        wp_enqueue_style('smartframe-admin-css', SMARTFRAME_PLUGIN_URL . '/admin/partials/css/admin.css?version=' . $scriptVersion);
        wp_enqueue_style('font-awesome-css-file', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css');

        wp_enqueue_script('valiate.js', SMARTFRAME_PLUGIN_URL . 'admin/partials/js/jqurey-validate.js?version=' . $scriptVersion, [], false, true);
        wp_enqueue_script('admin.js', SMARTFRAME_PLUGIN_URL . 'admin/partials/js/smartframe-admin.js?version=' . $scriptVersion, [], false, true);
        wp_enqueue_script('smartJs.js', SmartFrameOptionProviderFactory::create()->getWebComponentScriptUrl(), [], false, true);

        add_action('admin_head', [$this, 'jquery_cookie_enqueue_script']);
    }

    private function loadHooksWithValidApiKey()
    {
        add_action('activated_plugin', [new NotSupportedPluginsNotification(), 'checkUnsupportedPlugin']);
        add_action('admin_notices', [new NotSupportedPluginsNotification(), 'showNotification']);
        MediaLibraryManager::create()->loadHooks();
        EditAttachmentManager::create()->register();
    }

    public function adminNotificationActivateApiKey()
    {
        echo ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/notifications/activate-api-key.php', ['url' => PropertiesMenuHandler::menuLinkProvider()]);
    }

    public function jquery_cookie_enqueue_script()
    {
//remove cookies from last actiavtion pawel to ja
        if (get_option('smart_my_plugin_activation') === 'just-activated') {
            delete_option('smart_my_plugin_activation');
            wp_enqueue_script('smartframe-dectivation.js', SMARTFRAME_PLUGIN_URL . 'admin/partials/js/smartframe-dectivation.js', [], false, true);
        }

        $themeProvider = ThemeProvider::create();
        $themes = $themeProvider->provideKeyValueTheme();
        $newArray = [];

        array_walk($themes, function (&$v, $k) use (&$newArray) {
            $newArray[] = ['value' => $k, 'label' => $v];
        });
        ?>
        <script type='text/javascript'>
            var SmartFrameAvailableThemes = JSON.parse('<?php echo wp_json_encode($newArray) ?>');
            var SmartFrameCode = <?php echo SmartFrameApiFactory::create()->check_credentials() ? 1 : 0;?>;
            var SmartFrameUrl = {
                'wpSiteUrl': '<?php echo $_SERVER['HTTP_HOST'] ?>',
                'settingsPage': '<?php echo PropertiesMenuHandler::menuLinkProvider()?>',
                'upgradePlan': '<?php echo Config::instance()->getConfig('panel.upgradePlane');?>',
                'apiRegister': '<?php echo Config::instance()->getConfig('api.register-call');?>',
                'apiRegisterGuest': '<?php echo Config::instance()->getConfig('api.register-call-guest');?>',
                'apiActivate': '<?php echo Config::instance()->getConfig('api.activate-token');?>',
                'apiCheckAccessToken': '<?php echo Config::instance()->getConfig(SMARTFRAME_API_ENDPOINT);?>',
                'wpPluginApiUrl': '<?php echo Config::instance()->getConfig('wpPluginApiUrl');?>',
                'manageThemes': '<?php echo WordpressMenuUrlProvider::manageThemesUrl()?>'
            };
            var SmartFrameConvertAllImages = <?php echo SmartFrameOptionProviderFactory::create()->getEveryUpload() ? 1 : 0;?>;
            var SmartFrameSettings = {

                'userEmail': '<?php echo get_option('admin_email')?>',
                'accountIsActive': <?php echo SmartFrameApiFactory::create()->get_profile()->isActive() ? 'true' : 'false';?>,
                'isApiKeyCorrect': <?php echo SmartFrameApiFactory::create()->check_credentials() ? 'true' : 'false';?>,
            };
            var SmartFrameStorageExceeded = <?php echo SmartFrameApiFactory::create()->get_profile()->checkUserExceedStorageLimit() ? 1 : 0;?>;
        </script>
        <?php
    }
}