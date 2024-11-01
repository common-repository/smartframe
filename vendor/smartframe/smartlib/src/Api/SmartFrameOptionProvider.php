<?php

namespace SmartFrameLib\Api;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Converters\StringBoolean;

/**
 * Class SmartframeOptionProvider
 * @package SmartFrameLib\Api
 */
class SmartFrameOptionProvider
{

    /**
     * Prefix added to options
     * @var string
     */
    private $optionPrefix;

    const OPTION_MIN_WIDTH = '_minwidth';

    const OPTION_MIN_HEIGHT = '_minheight';

    const OPTION_API_KEY = '_apiKey';

    const OPTION_EVERY_UPLOAD = '_every_upload';

    const OPTION_ROLES = '_role';

    const WP_PLUGIN_API_KEY = 'WpPluginApiKey';

    const OPTION_THEME_FOR_SMARTFRAMES = '_theme_for_smartframe';

    const OPTION_DISABLED_CSS_CLASSES = '_option_disabled_css_classes';

    const OPTION_DISABLED_CSS_CLASSES_LIST = '_option_disabled_css_classes_list';

    const OPTION_ENABLE_CSS_CLASSES_LIST = '_option_enable_css_classes_list';

    const OPTION_GENERATED_ATTACHMENT_ID_BY_SMARTFRAME = '_id';

    const OPTION_ATTACHMENT_GENERATED_USING_API_KEY = '_attachment_generated_using_api_key';

    const OPTION_GENERATED_THUMB_URL_BY_SMARTFRAME = '_thumb_url';

    const OPTION_GENERATED_SRIPT_URL_BY_SMARTFRAME = '_script_url';

    const OPTION_USE_SMARTFRAME = '_use';

    const OPTION_ATTACHMENT_SMARTFRAME_SFM = '_sfm_url_attachment';

    const OPTION_API_KEY_VALID_ON_SAVE = '_valid_api_key_on_save';

    const OPTION_ATTACHMENT_SMARTFRAME_CAPTION = '_smartframe_caption';

    const OPTION_ATTACHMENT_SMARTFRAME_THEME = '_use_theme';

    public function getOptionUseSmartframe()
    {
        return $this->getOptionPrefix() . self::OPTION_USE_SMARTFRAME;
    }

    public function getOptionThemeForSmartframe()
    {
        return $this->getOptionPrefix() . self::OPTION_THEME_FOR_SMARTFRAMES;
    }

    public function getOptionDisabledCssClassesList()
    {
        return $this->getOptionPrefix() . self::OPTION_DISABLED_CSS_CLASSES_LIST;
    }

    public function getOptionEnableCssClassesList()
    {
        return $this->getOptionPrefix() . self::OPTION_ENABLE_CSS_CLASSES_LIST;
    }

    public function getOptionDisabledCssClasses()
    {
        return $this->getOptionPrefix() . self::OPTION_DISABLED_CSS_CLASSES;
    }

    public function getOptionMinWidth()
    {
        return $this->getOptionPrefix() . self::OPTION_MIN_WIDTH;
    }

    public function getOptionMinHeight()
    {
        return $this->getOptionPrefix() . self::OPTION_MIN_HEIGHT;
    }

    public function getOptionApiKey()
    {
        return $this->getOptionPrefix() . self::OPTION_API_KEY;
    }

    public function getOptionEveryUpload()
    {
        return $this->getOptionPrefix() . self::OPTION_EVERY_UPLOAD;
    }

    public function getOptionRoles()
    {
        return $this->getOptionPrefix() . self::OPTION_ROLES;
    }

    public function getOptionGeneratedAttachmentIdBySmartframe()
    {
        return $this->getOptionPrefix() . self::OPTION_GENERATED_ATTACHMENT_ID_BY_SMARTFRAME;
    }

    public function getOptionAttachmentGeneratedUsingApiKey()
    {
        return $this->getOptionPrefix() . self::OPTION_ATTACHMENT_GENERATED_USING_API_KEY;
    }

    public function getOptionGeneratedThumbUrlBySmartframe()
    {
        return $this->getOptionPrefix() . self::OPTION_GENERATED_THUMB_URL_BY_SMARTFRAME;
    }

    public function getOptionWebComponentScriptUrl()
    {
        return $this->getOptionPrefix() . self::OPTION_GENERATED_SRIPT_URL_BY_SMARTFRAME;
    }

    public function getOptionAttachmentUseSmartframe()
    {
        return $this->getOptionPrefix() . self::OPTION_USE_SMARTFRAME;
    }

    public function getOptionAttachmentSmartframeSfm()
    {
        return $this->getOptionPrefix() . self::OPTION_ATTACHMENT_SMARTFRAME_SFM;
    }

    public function getOptionApiKeyValidOnSave()
    {
        return $this->getOptionPrefix() . self::OPTION_API_KEY_VALID_ON_SAVE;
    }

    public function getOptionAttachmentSmartframeCaption()
    {
        return $this->getOptionPrefix() . self::OPTION_ATTACHMENT_SMARTFRAME_CAPTION;
    }

    public function getOptionAttachmentSmartframeTheme()
    {
        return $this->getOptionPrefix() . self::OPTION_ATTACHMENT_SMARTFRAME_THEME;
    }

    /**
     * SmartframeOptionProvider constructor.
     * @param $optionPrefix
     */
    public function __construct($optionPrefix)
    {
        $this->optionPrefix = $optionPrefix;
    }

    //Make all options have same prefix
    public function getThemeFromAttachment($attachmentId)
    {
        return get_post_meta($attachmentId, $this->getOptionAttachmentSmartframeTheme(), true);
    }

//Make all options have same prefix
    public function setThemeForAttachment($attachmentId, $theme)
    {
        update_post_meta($attachmentId, $this->getOptionAttachmentSmartframeTheme(), $theme);
    }

    //Make all options have same prefix
    public function getCaptionFromAttachment($attachmentId)
    {
        return get_post_meta($attachmentId, $this->getOptionAttachmentSmartframeCaption(), true);
    }

//Make all options have same prefix
    public function setCaptionForAttachment($attachmentId, $caption)
    {
        update_post_meta($attachmentId, $this->getOptionAttachmentSmartframeCaption(), $caption);
    }

    /**
     * @return string
     */
    public function getWebComponentScriptUrl()
    {
        return get_option($this->getOptionWebComponentScriptUrl());
    }

    /**
     * @return void
     */
    public function setWebComponentScriptUrl($scriptUrl)
    {
        update_option($this->getOptionWebComponentScriptUrl(), $scriptUrl);
    }

    /**
     * @param $attachmentId string
     * @return string
     */
    public function getGeneratedAttachmentIdBySmartFrame($attachmentId)
    {
        return get_post_meta($attachmentId, $this->getOptionGeneratedAttachmentIdBySmartframe(), true);
    }

    /**
     * @param $attachmentId string
     * @param $value
     * @return string
     */
    public function setGeneratedAttachmentIdBySmartFrame($attachmentId, $value)
    {
        return update_post_meta($attachmentId, $this->getOptionGeneratedAttachmentIdBySmartframe(), $value);
    }

    /**
     * @param $attachmentId string
     * @return string
     */
    public function getGeneratedThumbUrlBySmartFrame($attachmentId)
    {
        return get_post_meta($attachmentId, $this->getOptionPrefix() . self::OPTION_GENERATED_THUMB_URL_BY_SMARTFRAME, true);
    }

    /**
     * @param $attachmentId string
     * @param $value
     * @return string
     */
    public function setUseSmartFrame($value)
    {
        if (is_bool($value)) {
            return update_option($this->getOptionUseSmartframe(), StringBoolean::boolToString($value));
        }
        return update_option($this->getOptionUseSmartframe(), StringBoolean::validString($value));
    }

    /**
     * @param $attachmentId string
     * @return string
     */
    public function getUseSmartFrame($string = false)
    {
        $result = get_option($this->getOptionUseSmartframe(), StringBoolean::OPTION_YES);
        if ($string) {
            return $result;
        }
        return StringBoolean::stringToBool($result);
    }

    /**
     * @param $attachmentId string
     * @param $value
     * @return string
     */
    public function setAttachmentSmartFrameIdGeneratedUsingApiKey($attachmentId, $apiKey)
    {
        return update_post_meta($attachmentId, $this->getOptionPrefix() . self::OPTION_ATTACHMENT_GENERATED_USING_API_KEY, $apiKey);
    }

    /**
     * @param $attachmentId string
     * @return string
     */
    public function getAttachmentApiKeyUsedToGenerateSmartframe($attachmentId)
    {
        return get_post_meta($attachmentId, $this->getOptionPrefix() . self::OPTION_ATTACHMENT_GENERATED_USING_API_KEY, true);
    }

    /**
     * @param $attachmentId string
     * @param $value
     * @return string
     */
    public function setGeneratedThumbUrlBySmartFrame($attachmentId, $value)
    {
        return update_post_meta($attachmentId, $this->getOptionPrefix() . self::OPTION_GENERATED_THUMB_URL_BY_SMARTFRAME, $value);
    }

    /**
     * @param $attachmentId string
     * @return string
     */
    public function getAttachmentSmartFrameSfmUrl($attachmentId)
    {
        return get_post_meta($attachmentId, $this->getOptionPrefix() . self::OPTION_ATTACHMENT_SMARTFRAME_SFM, true);
    }

    /**
     * @param $attachmentId string
     * @param $value
     * @return string
     */
    public function setAttachmentSmartFrameSfmUrl($attachmentId, $value)
    {
        return update_post_meta($attachmentId, $this->getOptionPrefix() . self::OPTION_ATTACHMENT_SMARTFRAME_SFM, $value);
    }

    /**
     * @return string
     */
    public function getMinWidth()
    {
        return get_option($this->getOptionPrefix() . self::OPTION_MIN_WIDTH);
    }

    /**
     * @return string
     */
    public function setMinWidth($value)
    {
        return update_option($this->getOptionPrefix() . self::OPTION_MIN_WIDTH, $value);
    }

    /**
     * @return string
     */
    public function getMinHeight()
    {
        return get_option($this->getOptionPrefix() . self::OPTION_MIN_HEIGHT);
    }

    /**
     * @return string
     */
    public function setMinHeight($value)
    {
        return update_option($this->getOptionPrefix() . self::OPTION_MIN_HEIGHT, $value);
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return get_option($this->getOptionPrefix() . self::OPTION_API_KEY);
    }

    /**
     * @return string
     */
    public function setApiKey($key)
    {
        return update_option($this->getOptionPrefix() . self::OPTION_API_KEY, $key);
    }

    /**
     * @param bool $string
     * @return bool
     */
    public function getEveryUpload($string = false)
    {
        if ($string) {
            return get_option($this->getOptionPrefix() . self::OPTION_EVERY_UPLOAD);
        }
        return StringBoolean::stringToBool(get_option($this->getOptionPrefix() . self::OPTION_EVERY_UPLOAD));
    }

    /**
     * @param $value
     */
    public function setEveryUpload($value)
    {
        if (is_bool($value)) {
            return update_option($this->getOptionPrefix() . self::OPTION_EVERY_UPLOAD, StringBoolean::boolToString($value));
        }
        return update_option($this->getOptionPrefix() . self::OPTION_EVERY_UPLOAD, StringBoolean::validString($value));
    }

    /**
     * @return string
     */
    public function getEnabledRoles()
    {
        return get_option($this->getOptionPrefix() . self::OPTION_ROLES);
    }

    /**
     * @param $roles array
     * @return string
     */
    public function setEnabledRoles($roles)
    {
        return update_option($this->getOptionPrefix() . self::OPTION_ROLES, $roles);
    }

    /**
     * When user authenticate with api key one . We know that current used key is valid
     */
    public function setApiKeyWasValidOnSave($valid)
    {
        if (is_bool($valid)) {
            return update_option($this->getOptionPrefix() . self::OPTION_API_KEY_VALID_ON_SAVE, StringBoolean::boolToString($valid));
        }
        return update_option($this->getOptionPrefix() . self::OPTION_API_KEY_VALID_ON_SAVE, StringBoolean::validString($valid));
    }

    public function getApiKeyWasValidOnSave($asString = false)
    {
        if ($asString) {
            return get_option($this->getOptionPrefix() . self::OPTION_API_KEY_VALID_ON_SAVE);
        }
        return StringBoolean::stringToBool(get_option($this->getOptionPrefix() . self::OPTION_API_KEY_VALID_ON_SAVE));
    }

    /**
     * @return string
     */
    public function getOptionPrefix()
    {
        return $this->optionPrefix;
    }

    public function setDefaultSettings()
    {
        if ($this->getUseSmartFrame(true) === false) {
            if (get_option($this->getOptionPrefix() . self::OPTION_EVERY_UPLOAD) !== false) {
                $this->getEveryUpload() ? $this->setUseSmartFrame(true) : $this->setUseSmartFrame(false);
            } else if (get_option($this->getOptionPrefix() . self::OPTION_EVERY_UPLOAD) === false) {
                $this->setUseSmartFrame(true);
            }
        }

        delete_option($this->getOptionPrefix() . self::OPTION_EVERY_UPLOAD);
        delete_option($this->getOptionPrefix() . self::OPTION_MIN_HEIGHT);
        delete_option($this->getOptionPrefix() . self::OPTION_MIN_HEIGHT);
    }

    public function setWpPluginApiKey($apiKey)
    {
        return update_option($this->getOptionPrefix() . self::WP_PLUGIN_API_KEY, $apiKey);
    }

    public function getWpPluginApiKey()
    {
        return get_option($this->getOptionPrefix() . self::WP_PLUGIN_API_KEY);
    }

    /**
     * @param bool $string
     * @return string
     */
    public function getThemeForSmartframe()
    {
        return get_option($this->getOptionThemeForSmartframe());
    }

    /**
     * @param $value
     */
    public function setThemeForSmartframe($value)
    {
        return update_option($this->getOptionThemeForSmartframe(), $value);
    }

    /**
     * @param bool $string
     * @return bool
     */
    public function getDisabledCssClasses()
    {
        return get_option($this->getOptionDisabledCssClasses());
    }

    /**
     * @param $value
     */
    public function setDisabledCssClasses($value)
    {
        return update_option($this->getOptionDisabledCssClasses(), $value);
    }

    /**
     * @param bool $string
     * @return bool
     */
    public function getDisabledCssClassesList()
    {
        return get_option($this->getOptionDisabledCssClassesList());
    }

    /**
     * @param $value
     */
    public function setDisabledCssClassesList($value)
    {
        return update_option($this->getOptionDisabledCssClassesList(), $value);
    }

    /**
     * @param bool $string
     * @return bool
     */
    public function getEnabledCssClassesList()
    {
        return get_option($this->getOptionEnableCssClassesList());
    }

    /**
     * @param $value
     */
    public function setEnabledCssClassesList($value)
    {
        return update_option($this->getOptionEnableCssClassesList(), $value);
    }

}