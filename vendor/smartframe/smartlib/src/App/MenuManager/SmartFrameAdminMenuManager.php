<?php /** @noinspection ALL */

namespace SmartFrameLib\App\MenuManager;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\MenuHandlers\PropertiesMenuHandler;
use SmartFrameLib\App\MenuHandlers\RegisterMenuHandler;
use SmartFrameLib\App\MenuHandlers\ThemeMenuHandler;
use SmartFrameLib\App\MenuHandlers\TutorialMenuHandler;
use SmartFrameLib\App\Model\Payment;
use SmartFrameLib\App\Settings\Handlers\RegisterSettingsHandler;
use SmartFrameLib\Config\Config;
use SmartFrameLib\View\ViewRenderFactory;

class SmartFrameAdminMenuManager
{

    /**
     * @var self
     */
    private static $instance = null;

    const ADMIN_MENU_PREFIX = 'smart-frame-';
    /**
     * Name of Main menu that is displayed in menus in wordpress
     * @var string
     */
    const MENU_SLUG = SmartFrameAdminMenuManager::ADMIN_MENU_PREFIX . 'main-menu';

    /**
     * @var array
     */
    private $pages = [
        self::MENU_SLUG,
        PropertiesMenuHandler::MENU_SLUG,
        ThemeMenuHandler::MENU_SLUG,
        TutorialMenuHandler::MENU_SLUG,
        RegisterMenuHandler::MENU_SLUG,
    ];

    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function menuList()
    {
        return $this->pages;
    }

    public function loadMenu()
    {
        $registerMenuHandler = new RegisterMenuHandler();
        // Add the Universal Header.

        if ($this->isOnMenuPage()) {
            add_action('in_admin_header', [$this, 'loadAdminHeader'], 100);
        }

        add_filter('admin_footer_text', [$this, 'loadFooterForMenus'], 100);
        //show notifications for this pages

        add_menu_page("SmartFrame Settings", "SmartFrame" . $this->insertNotificationCountToMenu(), 'edit_posts', self::MENU_SLUG, ''
            , SMARTFRAME_PLUGIN_URL . '/admin/img/wp_icon@2x.png');

        add_submenu_page(self::MENU_SLUG, "Settings", 'Settings', 'edit_posts',
            self::MENU_SLUG, [new PropertiesMenuHandler(), 'display']
        );

//        if (!SmartFrameApiFactory::create()->get_profile()->isActive()) {
            add_submenu_page(self::MENU_SLUG, "Account", 'Account', 'edit_posts',
                RegisterMenuHandler::MENU_SLUG, [$registerMenuHandler, 'display']
            );
//        }

//        if (!empty(SmartFrameOptionProviderFactory::create()->getApiKey())) {
//            if ((isset($_POST['option_page']) && $_POST['option_page'] === RegisterSettingsHandler::SETTINGS_NAME)) {
//                add_submenu_page(RegisterMenuHandler::MENU_SLUG, "Register", 'Register', 'edit_posts',
//                    RegisterMenuHandler::MENU_SLUG, [$registerMenuHandler, 'display']
//                );
//            }
//        }

        $registerMenuHandler->isAfterPropertiesFormSave() ? $registerMenuHandler->updateOptionsAfterChangeApiKey() : "";

//        if ((isset($_GET['page']) && $_GET['page'] === RegisterMenuHandler::MENU_SLUG) &&
//            SmartFrameApiFactory::create()->get_profile()->isActive()) {
//            wp_redirect(site_url('/wp-admin/admin.php?page=' . self::MENU_SLUG));
//            exit;
//        }

        add_submenu_page(self::MENU_SLUG, "Examples", 'Examples', 'edit_posts',
            ThemeMenuHandler::MENU_SLUG, [new ThemeMenuHandler(), 'display']);

        add_submenu_page(self::MENU_SLUG, "Help", "Help", 'edit_posts',
            TutorialMenuHandler::MENU_SLUG, [new TutorialMenuHandler(), 'display']);

        if (isset($_GET['page']) && in_array($_GET['page'], $this->pages)) {
            add_action('admin_notices', function () {
                $_GET['settings-updated'] = 'true';
                settings_errors();
            });
        }
    }

    public static function create()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function loadFooterForMenus($text)
    {
        global $current_screen;
        if (!empty($current_screen->id) && strpos($current_screen->id, self::ADMIN_MENU_PREFIX) !== false && SmartFrameApiFactory::create()->check_credentials()) {
            return ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/footer.php')->render();
        }
        return '';
    }

    private function insertNotificationCountToMenu()
    {
        $profile = SmartFrameApiFactory::create()->get_profile();
        $optionProvider = SmartFrameOptionProviderFactory::create();

        $notificationCount = 0;
        if ($profile->checkUserExceedStorageLimit()) {
            $notificationCount++;
            add_action('admin_notices', function () {
                echo '<div id="setting-error-settings_storage" class="error settings-error notice"> 
                        <p><strong>
                        You reached the SmartFrame storage limit and some images can\'t be optimized. <a href="' . Config::instance()->getConfig('panel.upgradePlane') . '" target="_blank">Upgrade your plan</a>
                        </strong></p><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
            });
//            add_settings_error('general', 'settings_storage', 'You reached the freemium storage limit. <a href="' . Config::instance()->getConfig('panel.upgradePlane') . '" target="_blank">Upgrade your plan</a>', 'error');

        }

        if ($profile->getPayment()->getPaymentStatus() === Payment::STATUS_UNCAPTURED) {
            $notificationCount++;

            add_action('admin_notices', function () {
                echo '<div id="setting-error-settings_not_valid_payment" class="error settings-error notice "> 
                        <p><strong>Please update your payment details. If you don’t provide a valid credit card, your account will be suspended and all your content will be deleted. <a href="' . Config::instance()->getConfig('panel.upgradePlane') . '" target="_blank">Upgrade payment details</a></strong></p><button type="button" class="notice-dismiss"></button></div>';
            });
//            add_settings_error('general', 'settings_not_valid_payment', __('Please update your payment details. If you don’t provide a valid credit card, your account will be suspended and all your content will be deleted. Upgrade payment details'), 'error');
        }

        if (isset($_GET['srcr']) && $_GET['srcr'] === 'no-active' && !SmartFrameApiFactory::create()->get_profile()->isActive()) {
            add_action('admin_notices', function () {
                echo '<div  class="error settings-error notice is-dismissible">
                    <p>You need to be registered in order to manage appearance and remove the SmartFrame logo</p>
                    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                </div>';
            });
        }

        if ($optionProvider->getApiKeyWasValidOnSave()
            &&
            !SmartFrameApiFactory::create()->check_credentials() && !$this->isAfterPropertiesFormSave() && empty($_POST)
        ) {
            $notificationCount++;
            add_settings_error('general', 'settings_updated_wrong_api_key', __('The SmartFrame account with ' . $optionProvider->getApiKey() . ' access code (previously connected with this plugin) has been deleted.'), 'error');
        }

        if ($notificationCount !== 0) {
            return ' <span class="update-plugins count-1"><span class="plugin-count">' . $notificationCount . '</span></span>';
        }
        return '';
    }

    public function isAfterPropertiesFormSave()
    {
        return (isset($_GET['page']) && $_GET['page'] === SmartFrameAdminMenuManager::MENU_SLUG) &&
            (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true');
    }

    public function isOnMenuPage()
    {
        return isset($_GET['page']) && in_array($_GET['page'], $this->pages, true);
    }

    public function loadAdminHeader()
    {
        ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/parts/header.php')->display();
    }
}
