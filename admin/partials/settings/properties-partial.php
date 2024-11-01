<?php
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProvider;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\Providers\WordpressMenuUrlProvider;
use SmartFrameLib\App\Theme\ThemeProvider;
use SmartFrameLib\Converters\StringBoolean;

/** @var string $currentPlan */
/** @var string $storageUsed */
/** @var string $storageLimit */
/** @var string $percent */
/** @var string $settingsFields */
/** @var SmartFrameOptionProvider $option */
$option = SmartFrameOptionProviderFactory::create();
?>
<form action="options.php" method="post" id="smartframe--proporties-page-settings">

    <?php
    settings_fields($settingsFields);
    do_settings_sections(__FILE__);
    ?>

    <h2>Image protection and optimization</h2>
    <!--    <p>-->
    <!--        Configure how to optimize and secure images using the SmartFrame technology. Changes will be applied only to-->
    <!--        published images-->
    <!--        and you can always have the option to revert them in one click.-->
    <!--    </p>-->
    <table class="">
        <tbody>
        <tr>
            <td>
                <select id="properties-use-smartframe" style=""
                        name="<?php echo $option->getOptionUseSmartframe() ?>">
                    <option <?php echo $option->getUseSmartFrame() ? 'selected' : false ?>
                            value="<?php echo StringBoolean::OPTION_YES ?>">
                        Enabled
                    </option>
                    <option <?php echo $option->getUseSmartFrame() ? '' : 'selected' ?>
                            value="<?php echo StringBoolean::OPTION_NO ?>">
                        Disabled
                    </option>

                </select>
                <span id="smartframe-info-image-protection-select" style="padding: 3px;"
                      class="dashicons dashicons-info"></span>
            </td>
        </tr>
        </tbody>
    </table>
    <div id="smartframe-settings-area">
        <table class="form-table smartframe--settings-table">
            <tbody>
            <tr>
                <td style="margin-left: 25px;">
                    <input id="r1" class="smartframe--use-css-classes" type="radio" value="all_images"
                           name="<?php echo $option->getOptionDisabledCssClasses() ?>"
                        <?php echo $option->getDisabledCssClasses() === 'all_images' || empty($option->getDisabledCssClasses()) ? 'checked="checked"' : '' ?>/>
                    <label for="r1">
                        All published images
                    </label>
                </td>
                <td style="margin-left: 25px;">
                    <input id='r2' class="smartframe--use-css-classes" type="radio" value="include_images"
                           name="<?php echo $option->getOptionDisabledCssClasses() ?>"
                        <?php echo $option->getDisabledCssClasses() === 'include_images' ? 'checked="checked"' : '' ?>/>
                    <label for="r2">
                        All published images that contain the following CSS classes:
                    </label>


                    <input id="smartframe--use-css-classes-list" type="text"
                           name="<?php echo $option->getOptionEnableCssClassesList() ?>"
                           placeholder="my-css-class-1, my-css-class-2, my-css-class-3"
                           style="width: 350px;" value="<?php echo $option->getEnabledCssClassesList() ?>">
                    <span id="smartframe-info-enabled-css-classes" style="padding: 3px;"
                          class="dashicons dashicons-info"></span>
                </td>
                <td style="margin-left: 25px;">
                    <input id="r3" class="smartframe--use-css-classes" type="radio" value="exclude_images"
                           name="<?php echo $option->getOptionDisabledCssClasses() ?>"
                        <?php echo $option->getDisabledCssClasses() === 'exclude_images' ? 'checked="checked"' : '' ?>/>
                    <label for="r3">
                        All published images excluding those containing the following CSS classes:
                    </label>


                    <input id="smartframe--disable-css-classes-list" type="text"
                           name="<?php echo $option->getOptionDisabledCssClassesList() ?>"
                           placeholder="my-css-class-1, my-css-class-2, my-css-class-3"
                           style="width: 350px;" value="<?php echo $option->getDisabledCssClassesList() ?>">
                    <span id="smartframe-info-disabled-css-classes" style="padding: 3px;"
                          class="dashicons dashicons-info"></span>
                </td>
            </tr>

            <tr>
                <td style="margin-left: 25px;">
                    Theme: <select id="properties-theme"
                                        name="<?php echo $option->getOptionThemeForSmartframe() ?>">
                        <?php foreach (ThemeProvider::create()->provideKeyValueTheme() as $slug => $name): ?>
                            <option <?php echo $option->getThemeForSmartframe() === $slug ? 'selected' : false ?>
                                    value="<?php echo esc_html($slug) ?>"><?php echo $name ?></option>
                        <?php endforeach; ?>
                    </select>
                    <a style="margin-left: 5px;"
                       href="<?php echo WordpressMenuUrlProvider::manageThemesUrl() ?>"
                        <?php echo SmartFrameApiFactory::create()->get_profile()->isActive() ? 'target="_blank"' : '' ?>
                    >
                        <?php echo SmartFrameApiFactory::create()->get_profile()->isActive() ? 'Manage appearance' : 'Remove SmartFrame logo' ?>

                    </a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <!-- Link to open the modal -->

    <p class="submit">
        <a  id="smartframe-preview-page-button" target="_blank"
           href="<?php echo home_url(); ?>"
           class="button">Preview website</a>
        <input  type="submit" name="submit" id="submit" class="button button-primary"
               value="Save">
    </p>

</form>