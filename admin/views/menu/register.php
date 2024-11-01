<?php

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


<div class="wrap" style="overflow: hidden;">

    <?php if (!SmartFrameApiFactory::create()->get_profile()->isActive()): ?>
        <?php
        ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/inputs/apiKey.php', [
            'id' => 'sfm_smartframe' . '_apiKey',
            'optionName' => 'sfm_smartframe' . '_apiKey',
            'apiKey' => get_option('sfm_smartframe' . '_apiKey'),
            'keyOk' => SmartFrameApiFactory::create()->check_credentials(),
            'settingsFields' => $settingsFields,
        ])->display()
        ?>
    <?php endif; ?>
    <?php if (SmartFrameApiFactory::create()->get_profile()->isActive()): ?>
        <h2>Account</h2>
    <hr>
        <div class="progress-wrapper">
            <div class="progress-circle  p<?php echo $percent ?> <?php echo $percent >= 50 ? 'over50' : ''; ?>">
        <span class="progress-data">
            <span><?php echo $storageUsed ?></span> of
           <br>
            <span><?php echo $storageLimit ?></span>
        </span>
                <div class="left-half-clipper">
                    <div class="first50-bar"></div>
                    <div class="value-bar"></div>
                </div>
            </div>
            <div>
                <?php if (SmartFrameApiFactory::create()->get_profile()->isActive()): ?>
                    <p>
                        Email: <b class=""><?php echo $email ?></b>
                        </br>
                        Current plan: <b class=""><?php echo $currentPlan ?></b>
                    </p>
                <?php else: ?>
                    <p>
                        Account type: <b class="">Guest</b>
                        </br>
                        Current plan: <b class="">Free</b>
                    </p>
                <?php endif; ?>
                <?php if (SmartFrameApiFactory::create()->get_profile()->isActive()): ?>
                    <?php if (strtolower($currentPlan) === \SmartFrameLib\App\Model\ProfileModel::FREE_PLAN_NAME): ?>
                        <a href="https://panel.smartframe.cloud/account/upgrade-plan?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=<?php echo $_SERVER['HTTP_HOST'] ?>&utm_content=Upgrade"
                           target="_blank"
                           class="button button-primary">Upgrade</a>
                    <?php endif; ?>

                    <a href="https://panel.smartframe.cloud/login?utm_campaign=WordPress%20Plugin%20v2.2.0&utm_medium=referral&utm_source=<?php echo $_SERVER['HTTP_HOST'] ?>&utm_content=Go%20to%20my%20panel"
                       target="_blank"
                       class="button ">Go to my SmartFrame account</a>
                <?php else: ?>
                    <a href="<?php echo WordpressMenuUrlProvider::registerUrl() ?>"
                       class="button button-primary">Connect your SmartFrame account</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

