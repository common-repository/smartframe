<?php
if (!defined('ABSPATH')) exit;

/** @var string $optionName */

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Config\Config;

/** @var string $apiKey */
/** @var string $id */
/** @var boolean $keyOk */
?>
<div class="smartframe-status wide border" style="display: none;">
    <div class="create">
        <div id="smartframe--properties-page-register">
            <span class="spinner smartframe--loader"></span>
            <h4>New to SmartFrame?</h4>
            <form id="smartframe--proporties-page-register-form">
                <?php if (empty(\SmartFrameLib\Api\SmartFrameOptionProviderFactory::create()->getApiKey())): ?>
                    <p class="introduction">Create a free account and get access to additional features and 500MB cloud
                        space where your original images will be stored securely</p>
                <?php else: ?>
                    <p class="introduction">Create a free account to get access to the SmartFrame panel and manage all
                        additional features.</p>
                <?php endif; ?>

                <p><input type="text" name="smartframe-name" placeholder="First name"></p>
                <p><input type="text" name="smartframe-surname" placeholder="Surname"></p>
                <p><input type="text" name="smartframe-email" placeholder="Email address"></p>
                <p><input type="password" name="smartframe-password" placeholder="Set your password"></p>

                <p class="smartframe--privacy-policy">
                    <input for="privacy-policy" type="checkbox" id="smartframe-privacy-policy-id"
                           name="smartframe-privacy-policy">
                    <label for="smartframe-privacy-policy-id">I have read and accepted the
                        <a target="_blank"
                           href="https://smartframe.io/terms?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=<?php echo $_SERVER['HTTP_HOST'] ?>&utm_content=Terms%20of%20Use">Terms
                            of Use</a> and
                        <a href="https://smartframe.io/privacy-policy/?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=<?php echo $_SERVER['HTTP_HOST'] ?>&utm_content=Privacy%20Policy"
                           target="_blank">Privacy Policy</a>
                    </label>
                </p>
                <button class="button button-primary"> GET STARTED</button>
            </form>
        </div>

        <div id="smartframe--properties-page-active-token" style="display: none;position: relative;">
            <span class="spinner smartframe--loader"></span>
            <h4>Check your email</h4>
            <p style="word-wrap: break-word;">We have sent you an activation code to <span
                        id="smartframe-user-email"></span></p>

            <form id="smartframe--properties-page-active-token-form">
                <input placeholder="Activation code" type="text" name="smartframe-token">
                <button class="button button-primary">
                    Continue
                </button>
                <a href="#" style="display:block;text-decoration: none;margin-top: 20px;"
                   id="smartframe-back-to-register-form">Wrong email?</a>
            </form>
        </div>


    </div>

    <div style="display: none;" class="update">
        <h4>Already have a SmartFrame account?</h4>
        <p class="introduction"> Log in using your access code – you can find it in
            <a target="_blank"
               href="https://panel.smartframe.cloud/account/integration?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=<?php echo $_SERVER['HTTP_HOST'] ?>&utm_content=Get%20access%20code">Account
                settings > Integration</a>
        </p>


        <form action="options.php" method="post" id="smartframe--proporties-page">
            <?php
            settings_fields($settingsFields);
            do_settings_sections(__FILE__);
            ?>
            <input <?php //echo $keyOk ? 'disabled="disabled"' : ''; ?> type="text" name="<?php echo $optionName ?>"
                                                                        placeholder="Your access code"
                                                                        id="<?php echo $id ?>"
                                                                        value="<?php //echo $apiKey ?>">

            <button <?php //echo $keyOk ? 'disabled="disabled"' : ''; ?> class="button button-primary">
                LOG IN
            </button>

        </form>


        <form action="options.php" style="display: none;" method="post" id="smartframe--proporties-page-first-register">
            <?php
            settings_fields($settingsFields);
            do_settings_sections(__FILE__);
            ?>
            <input <?php //echo $keyOk ? 'disabled="disabled"' : ''; ?> type="text" name="<?php echo $optionName ?>"
                                                                        placeholder="Your access code"
                                                                        id="smartframe-without-valid-code"
                                                                        value="<?php //echo $apiKey ?>">

            <button <?php //echo $keyOk ? 'disabled="disabled"' : ''; ?> class="button button-primary">
                LOG IN
            </button>

        </form>
    </div>
</div>
