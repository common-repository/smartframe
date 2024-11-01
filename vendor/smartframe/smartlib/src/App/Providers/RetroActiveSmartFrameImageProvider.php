<?php

namespace SmartFrameLib\App\Providers;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;

class RetroActiveSmartFrameImageProvider
{

    private $imageId;

    private $optionProvider;

    private $api;

    public function __construct($imageId)
    {
        $this->imageId = $imageId;
        $this->optionProvider = SmartFrameOptionProviderFactory::create();
        $this->api = SmartFrameApiFactory::create();
    }

    public function canBeDisplayedAsSmartFrame()
    {
        return true; // ????? xD
    }

    public function getSmartFrameImageId()
    {
        //if image id is empty create smartframe
        if (empty($this->optionProvider->getGeneratedAttachmentIdBySmartFrame($this->imageId))) {
            $this->generateImage();
        }
//If apie key changed regenerate image with new api key
        if (
            $this->optionProvider->getAttachmentApiKeyUsedToGenerateSmartframe($this->imageId)
            !==
            $this->optionProvider->getApiKey()
        ) {
            //Generate new image beacuse this image can't be rendered using diffrent key
            $this->generateImage();
        }

        return $this->optionProvider->getGeneratedAttachmentIdBySmartFrame($this->imageId);
    }

    public function getTheme()
    {
        return $this->optionProvider->getThemeFromAttachment($this->imageId);
    }

    public function isConvertedToSmartFrame()
    {
        return !empty($this->optionProvider->getGeneratedAttachmentIdBySmartFrame($this->imageId)) &&
            ($this->optionProvider->getAttachmentApiKeyUsedToGenerateSmartframe($this->imageId)
                ===
                $this->optionProvider->getApiKey());
    }

    private function generateImage()
    {
        $account = $this->api->get_profile();
        if (!$account->checkUserExceedStorageLimit()) {
            if ($this->api->encode_image($this->imageId)) {
                $this->optionProvider->setAttachmentSmartFrameIdGeneratedUsingApiKey($this->imageId, $this->optionProvider->getApiKey());
                return true;
            }
        }

        return false;
    }
}