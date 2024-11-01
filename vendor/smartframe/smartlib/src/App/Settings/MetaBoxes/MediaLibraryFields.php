<?php

namespace SmartFrameLib\App\Settings\MetaBoxes;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\Model\ProfileModel;
use SmartFrameLib\App\Providers\SmartFrameImageProvider;
use SmartFrameLib\App\Providers\WordpressMenuUrlProvider;
use SmartFrameLib\App\Settings\MetaBoxes\Ajax\ApiAjaxWrapper;
use SmartFrameLib\App\Settings\MetaBoxes\Ajax\AttachmentsDetailsLoadSmartframePreview;
use SmartFrameLib\App\Settings\MetaBoxes\Ajax\MediaLibraryFieldsLoader;
use SmartFrameLib\App\Theme\ThemeProvider;
use SmartFrameLib\Config\Config;
use SmartFrameLib\Converters\StringBoolean;
use SmartFrameLib\View\ViewRenderFactory;

class MediaLibraryFields
{

    public static function create()
    {
        return new self();
    }

    public function register()
    {
        add_action('wp_ajax_getCaptionForAttachment', [new MediaLibraryFieldsLoader(), 'getCaptionForAttachment']);
        add_action('wp_ajax_saveCaptionForAttachment', [new MediaLibraryFieldsLoader(), 'saveCaptionForAttachment']);
        add_action('wp_ajax_revertCaptionForAttachment', [new MediaLibraryFieldsLoader(), 'revertCaptionForAttachment']);
        add_action('wp_ajax_previewCaptionForAttachment', [new MediaLibraryFieldsLoader(), 'previewCaptionForAttachment']);
        ;

        add_action('wp_enqueue_media', function () {
            remove_action('admin_footer', 'wp_print_media_templates');
            add_action('admin_footer', $func = function () {
                ob_start();
                wp_print_media_templates();
                $tpl = ob_get_clean();
                // To future-proof a bit, search first for the template and then for the section.
                if (($idx = strpos($tpl, 'tmpl-image-details')) !== false
                    && ($before_idx = strpos($tpl, '<div class="advanced-section">', $idx)) !== false) {
                    $tpl = substr_replace($tpl,
                        ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . 'admin/views/media-library/image-details-settings-section.php', [])->render()
                        , $before_idx, 0);
                }
                echo $tpl;
            });
        });

        if (preg_match('/\/wp-admin\/admin-ajax\.php|\/wp-admin\/async-upload.php/', $_SERVER['REQUEST_URI']) === 1) {
            add_filter('attachment_fields_to_save', [$this, 'addAttachmentForThemesAndSmartframeUpdate'], 10, 2);
            add_filter('attachment_fields_to_edit', [$this, 'addAttachmentForThemesAndSmartframeEdit'], 10, 2);
        }
    }

    function addAttachmentForThemesAndSmartframeEdit($form_fields, $post = null)
    {
        /** @var ProfileModel $profile */
        $profile = SmartFrameApiFactory::create()->get_profile();
        $optionProvider = SmartFrameOptionProviderFactory::create();
        $imageData = wp_get_attachment_image_src($post->ID, 'full');
        $row = "\t\t</br><p> <b>SMARTFRAME SETTINGS</b></p>";
        $form_fields_tr['smartframe-settings-header'] = [
            'tr' => $row,
            'show_for' => ['image\/jpeg', 'image\/png'],
            'show_in_edit' => false,
            'application' => 'image',
            'input' => false,
            'option_name' => false,
        ];

        $form_fields_tr['smartframe-settings-header-info'] = [
            'show_for' => ['image\/png', 'image\/jpeg'],
            'tr' => '<p id="smartframe--info-attachment-image">Configure how to secure and present this image with SmartFrame. Please note that SmartFrame supports JPEG only.</p>',
            'show_in_edit' => false,
            'application' => 'image',
            'input' => false,
            'option_name' => false,
        ];

        $disableInput[] = 'png';

        if ($profile->checkUserExceedStorageLimit() && $post->post_mime_type == 'image/jpeg') {
            $image = new SmartFrameImageProvider($post->ID);
            if (!$image->isConvertedToSmartFrame()) {
                $disableInput[] = 'jpeg';
            }
        }

        if (!$profile->checkUserExceedStorageLimit() && (320 > $imageData[1] || 120 > $imageData[2])) {
            $form_fields_tr[] = [
                'input' => 'html',
                'option_name' => $optionProvider->getOptionAttachmentSmartframeCaption(),

                'tr' => '<p class="smartframe--to-small-image">⚠️ SmartFrame theme and caption are not available when image dimensions are smaller than 320x120px.</p>',
                'class' => 'smartframe-use-smartframe-caption smart-fields',
                'application' => 'image',
                'exclusions' => ['audio', 'video', 'zip'],
                'show_in_edit' => false,
                'show_for' => ['image\/jpeg', 'image\/png'],
            ];
        }

        $form_fields[] = [
            'option_name' => $optionProvider->getOptionAttachmentUseSmartframe(),
            'disable_input_bool' => false,
            'label' => __('Use as SmartFrame', 'sfm_smartframe_use'),
            'input' => 'checkbox',
            'class' => 'smartframe-use-smartframe-checkbox smart-fields',
            'application' => 'image',
            'exclusions' => ['audio', 'video', 'zip'],
            'disable_input' => $disableInput,
            'show_in_edit' => false,
            'show_for' => ['image\/jpeg', 'image\/png'],
        ];

        if (320 > $imageData[1] || 120 > $imageData[2]) {
        } else {
            $form_fields[] = [
                'option_name' => $optionProvider->getOptionAttachmentSmartframeTheme(),
                'label' => __('SmartFrame theme', 'use_theme'),
                'input' => 'select',
                'class' => 'smartframe-theme-select smart-fields',
                'application' => 'image',
                'exclusions' => ['audio', 'video'],
                'disable_input' => array_merge(['image/png'], $disableInput),
//            'break_line_options' => ThemeProvider::create()->provideDefaultTheme(),
                'options' => ThemeProvider::create()->provideKeyValueTheme(),
                'show_in_edit' => false,
                'show_for' => ['image\/jpeg', 'image\/png'],
            ];
        }

        if (320 <= $imageData[1] && 120 < $imageData[2]) {
            $form_fields [] = [
                'option_name' => 'just_simple_html',
                'label' => '',
                'application' => 'image',
                'input' => 'html',
                'show_in_edit' => false,
                'application' => 'image',
                'file_type' => 'jpeg',
                'exclusions' => ['audio', 'video', 'zip'],
                'show_for' => ['image\/jpeg', 'image\/png'],
                'html' => '<a class="smartframe-manage-themes" href="'.WordpressMenuUrlProvider::manageThemesUrl().'" style="padding:5px;"class="smart-fields" target="_blank">Manage themes</a>',
            ];
        }

        if (320 > $imageData[1] || 120 > $imageData[2]) {
        } else {
            $form_fields[] = [
                'option_name' => $optionProvider->getOptionAttachmentSmartframeCaption(),
                'label' => __('SmartFrame caption', 'sfm_smartframe_caption'),
                'input' => 'text',
                'class' => 'smartframe-use-smartframe-caption smart-fields',
                'application' => 'image',
                'exclusions' => ['audio', 'video', 'zip'],
                'show_in_edit' => false,
                'show_for' => ['image\/jpeg', 'image\/png'],
                'disable_input' => array_merge(['image/png'], $disableInput),
            ];
        }
        if ($profile->checkUserExceedStorageLimit()) {
            $form_fields_tr['smartframe-settings-header-2'] = [
                'tr' => "\t\t<p class='attachment-modal-notification'>You reached the SmartFrame storage limit. <a href=\"" . Config::instance()->getConfig('panel.upgradePlane') . "\" target=\"_blank\">Upgrade your plan</a></p>",
                'show_in_edit' => false,
                'application' => 'image',
                'input' => false,
                'option_name' => false,
                'show_for' => ['image\/jpeg', 'image\/png'],
            ];
        }

        $renderer = new \SmartFrameLib\View\FormFieldsRenderer();

        $form_fields2 = array_filter($form_fields, function ($value) use ($post, $renderer) {
            return $renderer->canBeDisplayed($value, $post);
        });

        $form_fields2 = array_map(function ($value) use ($post, $renderer) {
            return $renderer->render($value, $post);
        }, $form_fields2);

        $form_fields_tr['inputs-all']['tr'] = $renderer->renderInOneRow($form_fields2);
        if (empty($form_fields_tr['inputs-all']['tr'])) {
            unset ($form_fields_tr['inputs-all']);
        }
        return $form_fields_tr;
    }

    function addAttachmentForThemesAndSmartframeUpdate($post, $attachment)
    {
//todo:We don't want to save this we need to discus this
        $optionProvider = SmartFrameOptionProviderFactory::create();

        $form_fields[] = [
            'option_name' => $optionProvider->getOptionAttachmentUseSmartframe(),
            'label' => __('Use smartframe', 'sfm_smartframe_use'),
            'input' => 'checkbox',
            'application' => 'image',
            'exclusions' => ['audio', 'video'],
        ];

        $form_fields[] = [
            'option_name' => $optionProvider->getOptionAttachmentSmartframeTheme(),
            'label' => __('Theme', 'use_theme'),
            'input' => 'select',

            'application' => 'image',
            'exclusions' => ['audio', 'video', 'zip'],
            'options' => ThemeProvider::create()->provideKeyValueTheme(),

        ];

        if (isset ($attachment[$optionProvider->getOptionAttachmentSmartframeCaption()])) {
            $form_fields[] = [
                'option_name' => $optionProvider->getOptionAttachmentSmartframeCaption(),
                'label' => __('SmartFrame <br> caption', 'sfm_smartframe_caption'),
                'input' => 'text',
                'class' => 'smartframe-use-smartframe-caption',
                'application' => 'image',
                'exclusions' => ['audio', 'video', 'zip', 'png'],
                'show_in_edit' => false,
            ];
            $imageModel = new \stdClass();
            $imageModel->metaData->description = wp_unslash($attachment[$optionProvider->getOptionAttachmentSmartframeCaption()]);
            $imageModel->name = SmartFrameOptionProviderFactory::create()->getGeneratedAttachmentIdBySmartFrame($post['ID']);
            SmartFrameOptionProviderFactory::create()->setCaptionForAttachment($post['ID'], $attachment[$optionProvider->getOptionAttachmentSmartframeCaption()]);
            unset($attachment[$optionProvider->getOptionAttachmentSmartframeCaption()]);
            try {
                SmartFrameApiFactory::create()->updateImageMetadata($imageModel);
            } catch (\Exception $e) {
            }
        }

        // If our fields array is not empty
        if (!empty($form_fields)) {
            // Browser those fields
            foreach ($form_fields as $values) {
                // If this field has been submitted (is present in the $attachment variable)
                if (isset($attachment[$values['option_name']])) {
                    // If submitted field is empty
                    // We add errors to the post object with the "error_text" parameter we set in the options
//                    if (strlen(trim($attachment[$values['option_name']])) == 0) {
//                        $post['errors'][$values['option_name']]['errors'][] = __($values['error_text']);
//                    } // Otherwise we update the custom field
//                    else {
                    update_post_meta($post['ID'], sanitize_key($values['option_name']), sanitize_text_field($attachment[$values['option_name']]));
                    if ($values['option_name'] === $optionProvider->getOptionAttachmentUseSmartframe()) {
                        if ($attachment[$values['option_name']] === StringBoolean::OPTION_YES) {
                            (new SmartFrameImageProvider($post['ID']))->getSmartFrameImageId();
                        }
                    }
//                    }
                } // Otherwise, we delete it if it already existed
                else {
                    if ($values['option_name'] === $optionProvider->getOptionAttachmentUseSmartframe()) {
                        SmartFrameOptionProviderFactory::create()->setAttachmentUseSmartFrame($post['ID'], StringBoolean::OPTION_NO);
                    } else {
                        //delete_post_meta($post['ID'], $values['option_name']);
                    }
                }
            }
        }

        return $post;
    }
}