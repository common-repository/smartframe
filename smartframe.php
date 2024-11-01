<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\App\MenuHandlers\PropertiesMenuHandler;
use SmartFrameLib\App\MenuManager\SmartFrameAdminMenuManager;
use SmartFrameLib\App\Statistics\CronScheduler;
use SmartFrameLib\App\Theme\ThemeProvider;
use SmartFrameLib\Config\Config;

define('SMARTFRAME_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SMARTFRAME_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SMARTFRAME_PLUGIN_BASE_NAME', plugin_basename(__FILE__));

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * Requirements:
 * PHP 5.5.0, reason: Vendor/GuzzleHTTP client
 *
 * @link              http://smartframe.io
 * @since             1.0.0
 * @package           SmartFrame
 *
 * @wordpress-plugin
 * Plugin Name:       SmartFrame - WordPress Image Security & Compression Plugin
 * Plugin URI:        https://smartframe.io/wordpress?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=WordPress%20plugins%20page&utm_content=Visit%20plugin%20site
 * Description:       Secure images with watermark, disable right click, enable zoom or fullscreen. Compress images without losing quality.
 * Version:           2.3
 * Author:            SmartFrame Technologies Ltd
 * Author URI:        https://smartframe.io/?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=WordPress%20plugins%20page&utm_content=Visit%20author%20site
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:
 * Domain Path:
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Load Composer dependecies
require 'vendor/autoload.php';

//Init GuttenBergBlocks
//require_once SMARTFRAME_PLUGIN_DIR . '/react/src/init.php';

\SmartFrameLib\App\SmartFramePlugin::create()->run();
//End Plugin :)

function redirectMySmartframe($plugin)
{
    if ($plugin == plugin_basename(__FILE__)) {
        wp_redirect(site_url('/wp-admin/admin.php?page=' . SmartFrameAdminMenuManager::MENU_SLUG));
        die;
    }
}

add_action('activated_plugin', 'redirectMySmartframe');

(new CronScheduler())->schedule();

//wp_cron();

