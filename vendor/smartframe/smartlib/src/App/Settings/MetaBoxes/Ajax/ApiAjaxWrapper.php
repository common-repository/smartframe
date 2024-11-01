<?php

namespace SmartFrameLib\App\Settings\MetaBoxes\Ajax;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\LazyLoadingSmartFrameApi;
use SmartFrameLib\Api\SmartFrameApi;
use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\Statistics\CronScheduler;
use SmartFrameLib\App\Statistics\StatisticsCollector;
use SmartFrameLib\Config\Config;

class ApiAjaxWrapper
{
    const UNSUPPORTED_PLUGIN_INFO = 'smartframe_unsuported_plugin_list';

    public function checkValidAccessCode()
    {
        $api = new LazyLoadingSmartFrameApi(new SmartFrameApi(Config::instance()->getConfig(SMARTFRAME_API_ENDPOINT), $_GET['apiKey']));
        $data['apiKeyValidation'] = true;

        if (!$api->check_credentials()) {
            $data['apiKeyValidation'] = false;
            $data['errors']['apiKeyValidation'] = 'Please provide a valid access code';
            wp_send_json($data, 200);
        }
        $profile = $api->get_profile();

        if (!empty($profile->getExternalImageSource())) {
            $data['errors']['externalImageSource'] = 'The access code you provided is already used for another WordPress site';
            $data['apiKeyValidation'] = false;
        }
        wp_send_json($data, 200);
    }

    public function connectNoRegisteredAccount()
    {
        if (is_admin()) {
            $api = SmartFrameApiFactory::create();
            $response = $api->connectNoRegisteredAccount($_GET);
            wp_send_json(json_decode($response->getBody()->getContents()), $response->getStatusCode());
        }
    }

    public function notSupportedPluginsNotificationStatus()
    {
        update_option(self::UNSUPPORTED_PLUGIN_INFO, true);
    }

    public function checkPrivacyPolicy()
    {
        update_option('smartframe_privacy_policy', true);
        (new StatisticsCollector())->sendCurrentSmartFrameThem(SmartFrameOptionProviderFactory::create()->getThemeForSmartframe());
        (new StatisticsCollector())->sendConversionStatus(SmartFrameOptionProviderFactory::create()->getUseSmartFrame());
        update_option(CronScheduler::ACTIVATION_TIME_OPTION, (new \DateTime())->format('Y-m-d H:i:s'));
    }

}