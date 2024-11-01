<?php

namespace SmartFrameLib\App\MenuHandlers;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\LazyLoadingSmartFrameApi;
use SmartFrameLib\Api\SmartFrameApi;
use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameApiInterface;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\MenuManager\SmartFrameAdminMenuManager;
use SmartFrameLib\App\Settings\Handlers\PropertiesSettingsHandler;
use SmartFrameLib\App\SmartFramePlugin;
use SmartFrameLib\App\Statistics\StatisticsCollector;
use SmartFrameLib\App\Theme\ThemeProvider;
use SmartFrameLib\Config\Config;
use SmartFrameLib\Converters\ByteSizeConverter;

class PropertiesMenuHandler
{
    const MENU_SLUG = SmartFrameAdminMenuManager::ADMIN_MENU_PREFIX . 'settings';

    /**
     * @var SmartFrameApiInterface
     */
    private $apiClient;

    public static function menuLinkProvider()
    {
        return admin_url('admin.php?page=' . SmartFrameAdminMenuManager::MENU_SLUG);
    }

    /**
     * DashboardMenuHandler constructor.
     */
    public function __construct()
    {
        $this->apiClient = SmartFrameApiFactory::create();
        //prepare settings fields to dispaly

        add_action('admin_init', [PropertiesSettingsHandler::create(), 'register_settings']);
        if ($this->isAfterPropertiesFormSave()) {
            $this->updateOptionsAfterChangeApiKey();
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function display()
    {
        $accountData = $this->apiClient->get_profile();

        return \SmartFrameLib\View\ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/settings/proporties.php',
            [
                'settingsFields' => SmartFramePlugin::provideName(),
                'apiKeyIsOk' => SmartFrameApiFactory::create()->check_credentials(),
                'apiKey' => SmartFrameOptionProviderFactory::create()->getApiKey(),
                'percent' => $accountData->getStorageUsedInPercent(),
                'storageLimit' => ByteSizeConverter::bytesToShortFormat($accountData->getStorageLimit(), 3),
                'storageUsed' => ByteSizeConverter::bytesToShortFormat($accountData->getStorageUsed(), 3),
                'currentPlan' => $accountData->getCurrentPlanName(),
                'email' => $accountData->getEmail(),
            ])->display();
    }

    /**
     * We can create class that handle the state of menu when is saved loaded etc.//todo
     * @return bool
     */
    public function isAfterPropertiesFormSave()
    {
//        return true;
        return (isset($_GET['page']) && ($_GET['page'] === SmartFrameAdminMenuManager::MENU_SLUG || $_GET['page'] === self::MENU_SLUG)) &&
            (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true');
    }

    public function isActiveMenu()
    {
        return (isset($_GET['page']) && $_GET['page'] === self::MENU_SLUG) ||
            (isset($_POST['option_page']) && $_POST['option_page'] === PropertiesSettingsHandler::SETTINGS_NAME);
    }

    public function updateOptionsAfterChangeApiKey()
    {
        $api = SmartFrameApiFactory::create(false);

        try {
            $theme = SmartFrameOptionProviderFactory::create()->getThemeForSmartframe();
            if (!in_array($theme, ThemeProvider::create()->provideThemesIds(), true)) {
                SmartFrameOptionProviderFactory::create()->setThemeForSmartframe(key(ThemeProvider::create()->provideDefaultTheme()));
            }
            (new StatisticsCollector())->sendConversionStatus(SmartFrameOptionProviderFactory::create()->getUseSmartFrame());
            (new StatisticsCollector())->sendCurrentSmartFrameThem($theme);
            $api->setExternalImageSource(['externalImageSource' => 'wordpress-plugin-image-source', 'wpPluginApiUrl' => Config::instance()->getConfig('wpPluginApiUrl')]);
            $account = $api->get_profile(true);
            SmartFrameOptionProviderFactory::create()->setWebComponentScriptUrl($account->getSmartframeJs());
            SmartFrameOptionProviderFactory::create()->setWpPluginApiKey($account->getWpPluginApiKey());
            SmartFrameOptionProviderFactory::create()->setApiKeyWasValidOnSave(true);
        } catch (\Exception $e) {
            SmartFrameOptionProviderFactory::create()->setApiKeyWasValidOnSave(false);
        }
    }
}