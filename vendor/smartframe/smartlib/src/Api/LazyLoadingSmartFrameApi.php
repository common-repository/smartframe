<?php

namespace SmartFrameLib\Api;
if (!defined('ABSPATH')) exit;

use Exception;
use GuzzleHttp\Exception\ClientException;
use SmartFrameLib\App\Model\ProfileModel;
use SmartFrameLib\App\Model\ProfileModelFactory;
use SmartFrameLib\App\Providers\SmartFrameImageProvider;
use SmartFrameLib\Config\Config;
use SmartFrameLib\Converters\StringBoolean;
use SmartFrameLib\Loger\FileLogger;

/**
 * Class LazyLoadingSmartFrameApi
 * @package SmartFrameLib\Api
 */
class LazyLoadingSmartFrameApi implements SmartFrameApiInterface
{

    /**
     * @var string
     */
    private $keszGroup = 'smartframe';

    /**
     * @var string
     */
    private $keszPrefix;
    /**
     * @var SmartFrameApi
     */
    private $smartFrameApi;

    /**
     * @var SmartframeOptionProvider
     */
    private $optionProvider;

    /**
     * @var string
     */
    private $smartFrameIdPrefix;

    /**
     * LazyLoadingSmartFrameApi constructor.
     * @param $smartFrameApiClient
     * @param string $prefixForImages
     */
    public function __construct($smartFrameApiClient, $prefixForImages = 'Wordpress-SmartFrame-Images')
    {
        $this->keszPrefix = uniqid('cache-', true);
        $this->optionProvider = SmartFrameOptionProviderFactory::create();
        $this->smartFrameIdPrefix = $prefixForImages;
        $this->smartFrameApi = $smartFrameApiClient;
    }

    /**
     * @return string
     */
    private function getCacheGroup()
    {
        return $this->keszPrefix . $this->keszGroup;
    }

    public function setExternalImageSource($data)
    {
        try {
            return $this->smartFrameApi->setExternalImageSource($data);
        } catch (Exception $e) {
            FileLogger::log($e->getMessage(), 'lazyloadingApi.txt');
        }
    }

    /**
     * @param $attachment_id
     * @param string $filename
     * @return bool
     */
    public function encode_image($attachment_id, $filename = '')
    {
        $image_exist = false;

        // get image to be encoded
        if ($filename != '') {
            $image = $filename;
        } else {
            $image = get_attached_file($attachment_id);
        }

        $imageModel = new  \stdClass();
        $imageModel->image = $image;
        $imageModel->name = $this->smartFrameIdPrefix . $attachment_id;

        list($width, $height, $type, $attr) = getimagesize($image);

        // check if JPEG and required min dimmensions, otherwise exit

        if ($type != 2) {
            $this->optionProvider->setAttachmentUseSmartFrame($attachment_id, StringBoolean::OPTION_NO);
            return false;
        }

        try {
            $response = $this->smartFrameApi->getImage($imageModel->name);
            if ($response->getStatusCode() == 200) {
                return false;
            }
        } catch (Exception $e) {
            FileLogger::log($e->getMessage(), 'lazyloadingApi.txt');
        }

        if (!$image_exist) {
            try {
                $response = $this->smartFrameApi->postImage($imageModel);

                // check the status code
                if ($response->getStatusCode() === 200) {
                    $responseArray = json_decode($response->getBody()->getContents(), true);
                    $this->optionProvider->setGeneratedAttachmentIdBySmartFrame($attachment_id, $responseArray['id']);
                    $this->optionProvider->setGeneratedThumbUrlBySmartFrame($attachment_id, $responseArray['thumbnailUrl']);

                    $link = sprintf('%s/%s.%s', Config::instance()->getConfig('static_cdn_sfm_url'),
                        SmartFrameApiFactory::create()->get_profile()->getPublicId() . '/' . $responseArray['id'],
                        'sfm');

                    $this->optionProvider->setAttachmentSmartFrameSfmUrl($attachment_id, $link);
                }
            } catch (Exception $e) {
                FileLogger::log($e->getMessage(), 'lazyloadingApi.txt');
                return false;
            }
        }
        return true;
    }

    /**
     *
     */
    public function encode_all_images()
    {
        // get all attachments
        $args = [
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_key' => '',
            'meta_value' => '',
            'post_type' => 'attachment',
            'post_mime_type' => '',
        ];
        $attachments = get_posts($args);

        foreach ($attachments as $attachment) {
            $this->encode_image($attachment->ID);
        }
    }

    /**
     * @param $attachment_id
     * @param string $filename
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update_image($attachment_id, $filename = '')
    {
        // get image data, then exit if the file is too small
        $image_data = wp_get_attachment_metadata($attachment_id);
        if ($image_data["width"] < $this->optionProvider->getMinWidth() && $image_data["height"] < $this->optionProvider->getMinHeight())
            return false;

        // get image to be encoded
        if ($filename != '') {
            $image = $filename;
        } else {
            $image = get_attached_file($attachment_id);
        }

        // /images POST uploads one image per request
        $imageModel = new  \stdClass();
        $imageModel->image = $image;
        $imageModel->name = $this->smartFrameIdPrefix . $attachment_id;

        try {
            $response = $this->smartFrameApi->updateImage($imageModel);
            if ($response !== null) {
                $responseArray = json_decode($response->getBody()->getContents(), true);
                $this->optionProvider->setGeneratedAttachmentIdBySmartFrame($attachment_id, $responseArray["id"]);
                $this->optionProvider->setGeneratedThumbUrlBySmartFrame($attachment_id, $responseArray["thumbnailUrl"]);
            }
        } catch (\Exception $e) {
//      echo 'Caught exception: ',  $e->getMessage(), "<br><br>";
            return false;
        }
        return true;
    }

    /**
     * @param $attachmentId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete_image($attachmentId)
    {
        return $this->smartFrameApi->deleteImage($attachmentId);
    }

    /**
     * @return ProfileModel
     */
    public function get_profile($refreshCache = false)
    {
        $keszKey = 'smartframe_profile_data';

        if ($refreshCache || !wp_cache_get($keszKey, $this->getCacheGroup())) {
            $response = '';
            try {
                $response = $this->smartFrameApi->getAccountInfo()->getBody()->getContents();
            } catch (Exception $e) {
                FileLogger::log($e->getMessage(), 'lazyloadingApi.txt');
            }
            $profile = ProfileModelFactory::createFromStringJson($response);
            wp_cache_add($keszKey, $profile, $this->getCacheGroup(), 60);
            return $profile;
        }
        return wp_cache_get($keszKey, $this->getCacheGroup());
    }

    /**
     * @return bool
     */
    public function check_credentials()
    {
        $keszKey = 'smartframe_account_check_credentials';

        try {
            if (!wp_cache_get($keszKey, $this->getCacheGroup())) {
                $this->smartFrameApi->getAccountInfo();
                wp_cache_add($keszKey, ['account' => true], $this->getCacheGroup(), 60);
                return true;
            }
            return wp_cache_get($keszKey, $this->getCacheGroup())['account'];
        } catch (Exception $exception) {
            wp_cache_add($keszKey, ['account' => false], $this->getCacheGroup(), 60);
            return false;
        }
    }

    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getThemes()
    {
        try {
            return json_decode($this->smartFrameApi->getAvailableListOfThemes()->getBody()->getContents());
        } catch (Exception $exception) {
            FileLogger::log($exception->getMessage(), 'lazyloadingApi.txt');
            return [];
        }
    }

    public function getImage($imageName)
    {
        return $this->smartFrameApi->getImage($imageName);
    }

    public function updateImageMetadata($imageModel)
    {
        $account = SmartFrameApiFactory::create()->get_profile();
        if ($account->getNewImageMetadataMode() === 'V2' && isset($imageModel->metaData->description)) {
            $imageModel->metaData->caption = $imageModel->metaData->description;
            unset($imageModel->metaData->description);
        }

        $result = $this->smartFrameApi->getImageMetadata($imageModel->name);
        if ($result === null) {
            FileLogger::log('updateImageMetadata:' . $imageModel->name . 'Image does not exits so we cant update metatags', 'LazyLoadingSmartFrameApi.log');
            return null;
        }
        $currentData = json_decode($result->getBody()->getContents());
        $imageModel->metaData = array_merge((array)$currentData->metadata, (array)$imageModel->metaData);

        return $this->smartFrameApi->updateImageMetadata($imageModel);
    }

    public function getImageMetadata($imageId)
    {
        return $this->smartFrameApi->getImageMetadata($imageId);
    }

    public function isSfmAvailable($getAttachmentSmartFrameSfmUrl)
    {
        try {
            $this->smartFrameApi->getSfmHead($getAttachmentSmartFrameSfmUrl);
            return true;
        } catch (Exception $e) {
            FileLogger::log($e->getMessage(), 'lazyloadingApi.txt');
            return $e;
        }
    }

    public function connectNoRegisteredAccount($data)
    {
        try {
            return $this->smartFrameApi->connectNoRegisteredAccount($data);
            return true;
        } catch (Exception $e) {
            FileLogger::log($e->getMessage(), 'lazyloadingApi.txt');
            return $e->getResponse();
        }
    }

    public function postStatisticsData($data)
    {
        try {
            return $this->smartFrameApi->postStatisticsData($data);
        } catch (Exception $e) {
            FileLogger::log($e->getMessage(), 'lazyloadingApi.txt');
        }
    }

}