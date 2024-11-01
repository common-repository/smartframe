<?php

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\App\Providers\WordpressMenuUrlProvider;
use SmartFrameLib\App\Theme\ThemeProvider;
use SmartFrameLib\Config\Config;

$profile = SmartFrameApiFactory::create()->get_profile();
?>


<div class="my_setting-section">
    <h2><?php _e('SMARTFRAME SETTINGS'); ?></h2>

    <div class="my_setting">

        <label style="margin-left: 20px;display:block;">
            In this section you can enable or disable SmartFrame and apply your favourite theme or
            caption. Please note that SmartFrame supports JPEG only.
        </label>


        <?php if (!$profile->checkUserExceedStorageLimit()): ?>
            <# if(data.model.toSmallImage === true) { #>
            <br>
            <label style="margin-left: 20px;display:block;">
                ⚠️ SmartFrame theme and caption are not available when
                image dimensions are smaller than 320x120px.
            </label>
            <# } #>
        <?php else: ?>
            <br>
            <label style="margin-left: 20px;display:block;color:red;">
                You reached the SmartFrame storage limit. <a
                        href="<?php echo Config::instance()->getConfig('panel.upgradePlane'); ?>" target="_blank">Upgrade
                    your
                    plan</a>
            </label>
        <?php endif; ?>

        <label class="setting my_setting sf-settings sf-details">
            <span><?php _e('Use as SmartFrame'); ?></span>
            <input type="checkbox" class="smartframe-use-smartframe-checkbox-edit-compat"
                   data-setting="use_smartframe"
                <?php echo $profile->checkUserExceedStorageLimit() ? 'disabled="disabled"' : ''; ?>
                   value="{{ data.model.use_smartframe }}"/>
        </label>
        <# if(data.model.toSmallImage === false) { #>


        <label class="setting my_setting sf-settings sf-details">
            <span><?php _e('SmartFrame theme'); ?></span>
            <select class="smartframe--attachments--options" data-setting="theme_name"
                    value="{{ data.model.theme_name }}"
                    disabled="<?php echo $profile->checkUserExceedStorageLimit() ? 'disabled' : ''; ?>">
                <?php foreach (ThemeProvider::create()->provideKeyValueTheme() as $slug => $name): ?>
                    <option value="<?php echo $slug ?>"><?php echo $name ?></option>
                <?php endforeach; ?>
            </select>

        </label>

        <label class="setting my_setting sf-settings sf-details" style="overflow: hidden;">
            <span>&nbsp;</span> <a
                    href="<?php echo WordpressMenuUrlProvider::manageThemesUrl()?>"
                    target="_blank">
                Manage themes
            </a>
        </label>
        <label class="setting my_setting sf-settings sf-details">
            <span><?php _e('SmartFrame Caption'); ?></span>
            <input type="text" id="smartframe--caption" class="smartframe--caption"
                   data-setting="smartframe_caption"
                   maxlength="1000"
                   value="{{ data.model.smartframe_caption }}"
                   disabled="<?php echo $profile->checkUserExceedStorageLimit() ? 'disabled' : ''; ?>"
            />
        </label>
        <# } #>
    </div>
</div>
