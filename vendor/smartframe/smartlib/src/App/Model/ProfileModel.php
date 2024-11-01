<?php

namespace SmartFrameLib\App\Model;
if (!defined('ABSPATH')) exit;

/**
 * Class ProfileModel
 * @package SmartFrameLib\App\Model
 */
class ProfileModel
{

    const FREE_PLAN_NAME = 'free';

    /**
     * @var string
     */
    private $accountName;
    /**
     * @var string
     */
    private $publicId;

    /**
     * @var string
     */
    private $currentPlanName;
    /**
     * @var int
     */
    private $storageLimit;
    /**
     * @var string
     */
    private $smartframeJs;
    /**
     * @var float value is in percent
     */
    private $storageUsed;

    /**
     * @var Payment
     */
    private $payment;
    /**
     * @var string
     */
    private $email;

    /**
     * @var bool
     */
    private $newImageMetadataMode;

    /**
     * @var bool
     */
    private $isActive;

    private $wpPluginApiKey;

    private $externalImageSource;

    private $storageUsedInBytes;

    /**
     * ProfileModel constructor.
     * @param \stdClass
     */
    public function __construct($variables)
    {
        $this->storageLimit = $variables['currentPlan']['storageLimit'];
        $this->storageUsed = $variables['storageUsed'];
        $this->currentPlanName = $variables['currentPlan']['name'];
        $this->accountName = $variables['name'];
        $this->publicId = $variables['publicId'];
        $this->smartframeJs = $variables['smartframejs'];
        $this->email = $variables['email'];
        $this->newImageMetadataMode = $variables['newImageMetadataMode'];
        $this->isActive = $variables['isActive'];
        $this->wpPluginApiKey = $variables['wpPluginApiKey'];
        $this->storageUsedInBytes = $variables['storageUsedInBytes'];
        $this->externalImageSource = isset($variables['externalImageSource']) ? $variables['externalImageSource'] : '';

        $this->payment = new Payment($variables['payment']);
    }

    /**
     * @return string
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * @return string
     */
    public function getPublicId()
    {
        return $this->publicId;
    }

    /**
     * @return string
     */
    public function getCurrentPlanName()
    {
        return $this->currentPlanName;
    }

    /**
     * @return int
     */
    public function getStorageLimit()
    {
        return $this->storageLimit;
    }

    /**
     * @return string
     */
    public function getSmartframeJs()
    {
        return $this->smartframeJs;
    }

    /**
     * @return float
     */
    public function getStorageUsed($precision = 0)
    {
        return round($this->getStorageUsedInBytes(), $precision);
    }

    public function canImageBeTransformed($imageSize)
    {
        return ($this->getStorageUsed() + $imageSize) <= (int)$this->getStorageLimit();
    }

    /**
     * @return float
     */
    public function getStorageUsedInPercent($precision = 0)
    {
        return round($this->storageUsed, $precision);
    }

    public function checkUserExceedStorageLimit()
    {
        return $this->getStorageUsedInPercent() >= 99 && $this->isFreeUser();
    }

    public function isFreeUser()
    {
        return strtolower($this->getCurrentPlanName()) === self::FREE_PLAN_NAME;
    }

    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return bool
     */
    public function isNewImageMetadataMode()
    {
        return $this->newImageMetadataMode;
    }

    /**
     * @param string $newImageMetadataMode
     */
    public function getNewImageMetadataMode()
    {
        return $this->newImageMetadataMode;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return mixed
     */
    public function getWpPluginApiKey()
    {
        return $this->wpPluginApiKey;
    }

    /**
     * @return mixed
     */
    public function getExternalImageSource()
    {
        return $this->externalImageSource;
    }

    /**
     * @return mixed
     */
    public function getStorageUsedInBytes()
    {
        return $this->storageUsedInBytes;
    }

    /**
     * @return mixed
     */
    public function modifyStorageUsedInBytes($bytes)
    {
        $this->storageUsedInBytes += $bytes;
        return $this;
    }
}