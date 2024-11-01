<?php

namespace SmartFrameLib\App\Settings\Handlers;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\SmartFrameApi;
use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProvider;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\Statistics\CronScheduler;
use SmartFrameLib\App\Statistics\StatisticsCollector;
use SmartFrameLib\Config\Config;

class PropertiesSettingsHandler
{

    const SETTINGS_NAME = 'sfm-smartframe';

    private $option_name = 'sfm_smartframe';
    /**
     * @var SmartFrameOptionProvider
     */
    private $configProvider;

    public function __construct()
    {
        $this->configProvider = SmartFrameOptionProviderFactory::create();
    }

    public static function create()
    {
        return new self();
    }

    public function register_settings()
    {
        if (!SmartFrameApiFactory::create()->check_credentials()) {
            $this->onSaveProperties();
        }

        if (SmartFrameApiFactory::create()->check_credentials() && $this->isSaveProporties()) {
            register_setting(self::SETTINGS_NAME, $this->configProvider->getOptionDisabledCssClassesList(), [$this, 'sanitizeCssClassList']);
            register_setting(self::SETTINGS_NAME, $this->configProvider->getOptionUseSmartframe(), [$this, 'sfm_smartframe_sanitize_checkbox']);
            register_setting(self::SETTINGS_NAME, $this->configProvider->getOptionThemeForSmartframe(), [$this, 'sfm_smartframe_sanitize_checkbox']);
            register_setting(self::SETTINGS_NAME, $this->configProvider->getOptionDisabledCssClasses(), [$this, 'sfm_smartframe_sanitize_checkbox']);
            register_setting(self::SETTINGS_NAME, $this->configProvider->getOptionEnableCssClassesList(), [$this, 'sanitizeCssClassList']);
        }
    }

    /**
     * Sanitize the text field value before being saved to database
     *
     * @param string $text $_POST value
     * @return string           Sanitized value
     * @throws \Exception
     * @since  1.0.0
     */
    public function sfm_smartframe_validate_api($accescode)
    {
        $api = new SmartFrameApi(
            Config::instance()->getConfig(SMARTFRAME_API_ENDPOINT),
            $accescode
        );

        try {
            $result = $api->getAccountInfo();
            add_settings_error('my-errors', 'settings_updated_wrong_api_key_2', 'Success! 👍🏼 You can now optimize and secure your images with SmartFrame.', 'updated');
            return sanitize_text_field($accescode);
        } catch (\Exception $e) {
            add_settings_error('my-errors', 'settings_updated_wrong_api_key_2', __('Please provide a valid access code'), 'error');
        }
        return '';
    }

    /**
     * Sanitize checkbox value before being saved to database
     *
     * @param string $position $_POST value
     * @return string           Sanitized value
     * @since  1.0.0
     */
    public function sfm_smartframe_sanitize_checkbox($checkbox_value)
    {
        return sanitize_text_field($checkbox_value);
    }

    public function sanitizeCssClassList($checkbox_value)
    {
        return $checkbox_value;
    }

    private function isSaveProporties()
    {
        return (isset($_POST['option_page']) && $_POST['option_page'] === self::SETTINGS_NAME);
    }

//Only run when this page was saved
    private function onSaveProperties()
    {
        if ($this->isSaveProporties()) {
            add_action('updated_option', function ($option_name, $option_value) {
                if ($option_name === SmartFrameOptionProviderFactory::create()->getOptionApiKey()) {
                    update_option(CronScheduler::ACTIVATION_TIME_OPTION, (new \DateTime())->format('Y-m-d H:i:s'));
                }
            }, 10, 2);

            add_settings_field(
                $this->option_name . '_apiKey',
                'Api Key',
                function () {
                    \SmartFrameLib\View\ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/inputs/apiKey.php', [
                        'id' => $this->option_name . '_apiKey',
                        'optionName' => $this->option_name . '_apiKey',
                        'apiKey' => get_option($this->option_name . '_apiKey'),
                        'keyOk' => SmartFrameApiFactory::create()->check_credentials(),
                    ])->display();
                },
                self::SETTINGS_NAME,
                $this->option_name . '_api',
                ['label_for' => $this->option_name . '_apiKey']
            );

            register_setting(self::SETTINGS_NAME, $this->option_name . '_apiKey', [$this, 'sfm_smartframe_validate_api']);
        }
    }

}