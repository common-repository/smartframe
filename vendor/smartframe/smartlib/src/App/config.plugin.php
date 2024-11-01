<?php

use SmartFrameLib\Config\Config;
const SMARTFRAME_API_ENDPOINT = 'endpoint';

$config = Config::instance();

//CSS AND JS Versioning
$config->addConfig('scripts-version', '2.2.0-prod-build-2');

$config->addConfig('DEBUG', false);
$config->addConfig('wpPluginApiUrl', $_SERVER['HTTP_HOST'] . '?rest_route=/smartframe/v1/images-data');
$config->addConfig('panel.endpoint', 'https://panel.smartframe.io');
$config->addConfig(SMARTFRAME_API_ENDPOINT, 'https://api2.smartframe.io/v1');
$config->addConfig('static_cdn_sfm_url', 'https://static.smartframe.io/sfm');
$config->addConfig('panel.theme', $config->getConfig('panel.endpoint') . '/theme/manage');
$config->addConfig('panel.login', 'http://bit.ly/2CpmEuS');
$config->addConfig('panel.upgradePlane', $config->getConfig('panel.endpoint') . '/account/upgrade-plan?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source='.$_SERVER['HTTP_HOST'].'&utm_content=Upgrade%20Plan');
$config->addConfig('panel.register', $config->getConfig('panel.endpoint') . '/login');
$config->addConfig('panel.accessCode', 'http://bit.ly/2QS8Tdh');
$config->addConfig('api.activate-token', 'https://panel.smartframe.io/api-activate');
$config->addConfig('api.register-call', $config->getConfig('panel.endpoint') .'/api-register?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=WordPress Plugin - ' . $_SERVER['HTTP_HOST'] . '&utm_content=Get%20started%20button');
$config->addConfig('api.register-call-guest', $config->getConfig('panel.endpoint') .'/api-register?utm_campaign=WordPress%20Plugin%20v2.2.0%20-%20Guest%20user&utm_medium=referral&utm_source=WordPress Plugin - ' . $_SERVER['HTTP_HOST'] . '&utm_content=Skip%20registration%20button');
$config->addConfig('api.statistics.endpoint', 'https://statistics.smartframe.io'); //check this url after deploy changes to cloud