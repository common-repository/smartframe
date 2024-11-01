<?php
if (!defined('ABSPATH')) exit;

//@todo:add some more info about this variables

/** @var string $settingsFields */

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\App\Providers\WordpressMenuUrlProvider;
use SmartFrameLib\View\ViewRenderFactory;

/** @var bool $apiKeyIsOk */
/** @var string $apiKey */
/** @var string $currentPlan */
/** @var string $storageUsed */
/** @var string $storageLimit */
/** @var string $percent */
/** @var string $email */

?>

<?php if (empty($apiKey) || get_option('smartframe_privacy_policy') === false): ?>
    <div class="notice notice-warning" style="position:relative;">
        <p>
            <input type="checkbox" id="smartframe-no-register-checkbox">
            Check here to accept the
            <a target="_blank"
               href="https://smartframe.io/terms?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=<?php echo $_SERVER['HTTP_HOST'] ?>&utm_content=Terms%20of%20Use">
                SmartFrame Terms of Use</a>
            and
            <a href="https://smartframe.io/privacy-policy/?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=<?php echo $_SERVER['HTTP_HOST'] ?>&utm_content=Privacy%20Policy"
               target="_blank">Privacy Policy</a>
            <span id="smartframe--register-spinner" class="spinner" style="float: none;margin: 0 0 0 10px;"></span>
        </p>
    </div>
<?php endif; ?>

<div class="wrap">
    <h1>Settings</h1>
    <hr>
    <?php if (empty($apiKey) || (get_option('smartframe_privacy_policy') === false)): ?>
    <div class="smartframe--options-content">
        <div class="smartframe--empty-overlay"></div>
        <?php endif; ?>

        <?php
        ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/settings/properties-partial.php', [
            'currentPlan' => $currentPlan,
            'percent' => $percent,
            'settingsFields' => $settingsFields,
        ])->display();

        ?>



        <?php if (empty($apiKey) || (get_option('smartframe_privacy_policy') === false)): ?>
    </div>
<?php endif; ?>


    <form action="options.php" method="post" id="smartframe--proporties-page-no-register" style="display: none;">
        <?php
        settings_fields($settingsFields);
        do_settings_sections(__FILE__);
        ?>
        <input type="text"
               name="<?php echo \SmartFrameLib\Api\SmartFrameOptionProviderFactory::create()->getOptionApiKey() ?>"
               placeholder="Your access code"
               id="sfm_smartframe_apiKey_no_register"
               value="<?php echo $apiKey ?>">

        <button class="button button-primary">
            LOGIN
        </button>
    </form>
