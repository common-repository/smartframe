<?php

namespace SmartFrameLib\App\Settings\Handlers;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\SmartFrameApi;
use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\Statistics\CronScheduler;
use SmartFrameLib\App\Statistics\StatisticsCollector;
use SmartFrameLib\Config\Config;

class RegisterSettingsHandler
{

    const SETTINGS_NAME = 'sfm-smartframe-2';

    private $option_name = 'sfm_smartframe';

    public static function create()
    {
        return new self();
    }

    public function register_settings()
    {
        $this->onSaveProperties();
        // Add API section

    }

    /**
     * Render the text for the api section
     *
     * @since  1.0.0
     */
    public function sfm_smartframe_api_cb()
    {
        echo '<p>' . __('Please enter your <b>SmartFrame.io</b> account details.', 'sfm-smartframe') . '</p>';
    }

    private function onSaveProperties()
    {
        if (isset($_POST['option_page']) && $_POST['option_page'] === self::SETTINGS_NAME) {
            add_action('updated_option', function ($option_name, $option_value) {
                if ($option_name === SmartFrameOptionProviderFactory::create()->getOptionApiKey()) {
                        update_option(CronScheduler::ACTIVATION_TIME_OPTION,(new \DateTime())->format('Y-m-d H:i:s'));
                }
            }, 10, 2);

            add_settings_section(
                $this->option_name . '_api', 'API', [$this, $this->option_name . '_api_cb'], self::SETTINGS_NAME
            );

            register_setting(self::SETTINGS_NAME, $this->option_name . '_apiKey', [$this, $this->option_name . '_validate_api']);
        }
    }

    /**
     * Render the text for the options section
     *
     * @since  1.0.0
     */
    public function sfm_smartframe_options_cb()
    {
        echo '<p>' . __('Please enter options.', 'sfm-smartframe') . '</p>';
    }

    /**
     * Sanitize the text field value before being saved to database
     *
     * @param string $text $_POST value
     * @return string           Sanitized value
     * @since  1.0.0
     */
    public function sfm_smartframe_sanitize_textfield($text)
    {
        return sanitize_text_field($text);
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


       $apiKey =  SmartFrameOptionProviderFactory::create()->getApiKey();
        try {
            $result = $api->getAccountInfo();

            if (empty($apiKey)){
                add_settings_error('my-errors', 'settings_updated_wrong_api_key_2', 'Success! 👍🏼 You now have access to the SmartFrame panel if you want to manage all the additional features.', 'updated');
            }else{
                add_settings_error('my-errors', 'settings_updated_wrong_api_key_2', 'Success! 👍🏼 The SmartFrame logo has been removed from your images. You now have access to the SmartFrame panel if you want to manage all the additional features.', 'updated');
            }
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

}