<?php

namespace SmartFrameLib\App\Providers;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;

use SmartFrameLib\Loger\FileLogger;

class SmartFrameImageProvider
{

    private static $profile = null;

    private $imageId;

    private $optionProvider;

    private $api;

    public function __construct($imageId)
    {
        $this->imageId = $imageId;
        $this->optionProvider = SmartFrameOptionProviderFactory::create();
        $this->api = SmartFrameApiFactory::create();
        if (!self::$profile) {
            self::$profile = $this->api->get_profile();
        }
    }

    public function canBeDisplayed($imageUrl)
    {
        $result = false;

        $fileName = $this->prepareFileName($imageUrl);
        $imageUrl = str_replace(substr($imageUrl, strrpos($imageUrl, '/') + 1), $fileName, $imageUrl);
        $imageSize = $this->getImageSize(preg_replace('/localhost:8080/', 'localhost', $imageUrl));

        $imageStringToLogger = sprintf("ImageSize:%s CurrentCapacity:%s CapacityLimit:%s ImageUrl:%s \n", $imageSize, self::$profile->getStorageUsed(), self::$profile->getStorageLimit(), $imageUrl);
        FileLogger::log($imageStringToLogger, 'image-size-log.txt');

        if (self::$profile->canImageBeTransformed($imageSize)) {
            FileLogger::log("Image Can Be Transformed FREE SPACE \n", 'image-size-log.txt');
            $result = true;
            if (!$this->haveHashedId($fileName) && !$this->wasGenereatedUsingSameApiKey($fileName)) {
                if ($this->wasGenereatedUsingSameApiKey($fileName)) {
                    FileLogger::log(sprintf("USING SAME API KEY: Have hashed ID:%s \n", $this->getImageData($fileName)->hashed_id), 'image-size-log.txt');
                } else {
                    FileLogger::log(sprintf("Didn't have hashed ID: \n"), 'image-size-log.txt');
                }
                self::$profile->modifyStorageUsedInBytes($imageSize);
            } else {
                FileLogger::log(sprintf("Have hashed ID:%s \n", $this->getImageData($fileName)->hashed_id), 'image-size-log.txt');
            }
        } else {
            if ($this->haveHashedId($fileName)) {
                if ($this->wasGenereatedUsingSameApiKey($fileName)) {
                    $result = true;
                    FileLogger::log(sprintf("Image Can Be Transformed HASHED ID IMAGE: %s\n", $this->getImageData($fileName)->hashed_id), 'image-size-log.txt');
                }
            }
        }

        if (!$result) {
            FileLogger::log("Image Can't Be Transformed\n", 'image-size-log.txt');
        }
        FileLogger::log(sprintf("---------------------\n"), 'image-size-log.txt');

        return $result;
    }

    public function generateDataForExistingImages($imageUrl, $hashedId)
    {
        $fileName = $this->prepareFileName($imageUrl);
        $this->storeHashedId($hashedId, $fileName);
    }

    public function generateHashedId($imageUrl)
    {
        $fileName = $this->prepareFileName($imageUrl);
        $hashedId = $this->prepareHashedId($fileName);
        $this->storeHashedId($hashedId, $fileName);

        return $hashedId;
    }

    public function prepareFileName($imageUrl)
    {
        //preapre file name
        $post = get_post($this->imageId);

        $originalFileName = preg_replace('/\.jpg+(.+|)/', '', substr($post->guid, strrpos($post->guid, '/') + 1));
        $fileName = preg_replace('/\.jpg+(.+|)/', '', substr($imageUrl, strrpos($imageUrl, '/') + 1));
        $fileName = str_replace($originalFileName, '', $fileName);
        $fileName = preg_replace('/-+[0-9]+x+[0-9]+(?:.(?!(-)))+$/', '', $fileName);
        $fileName = $originalFileName . $fileName . '.jpg';

        return $fileName;
    }

    public function prepareHashedId($fileName)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'smartframe_image';
        $imageProxy = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE image_id='%s' AND file_name='%s'", $tableName, $this->imageId, $fileName));

        //if not prepare some logic
        if (!$imageProxy && !$this->wasGenereatedUsingSameApiKey($fileName)) {
            $hashedId = md5($fileName . time() . mt_rand(990, 99999) . mt_rand(990, 99999) . mt_rand(990, 99999) . mt_rand(990, 99999));
        } else {
            if ($this->wasGenereatedUsingSameApiKey($fileName)) {
                $hashedId = $imageProxy[0]->hashed_id;
            } else {
                $wpdb->update($tableName, ['api_key' => SmartFrameOptionProviderFactory::create()->getApiKey()], ['id' => $imageProxy[0]->id]);
                $hashedId = $imageProxy[0]->hashed_id;
            }
        }
        return $hashedId;
    }

    public function storeHashedId($hashedId, $fileName)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'smartframe_image';
        $imageProxy = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE image_id='%s' AND file_name='%s'", $tableName, $this->imageId, $fileName));

        if ($imageProxy) {
            return; //if hashed id exists in DB stop storing
        }
        $wpdb->insert($tableName, ['image_id' => $this->imageId, 'hashed_id' => $hashedId, 'api_key' => SmartFrameOptionProviderFactory::create()->getApiKey()]);
        $this->buildGenerationData($hashedId, $fileName);
    }

    public function getImageData($fileName)
    {
        global $wpdb;

        $tableName = $wpdb->prefix . 'smartframe_image';
        $imageProxy = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE image_id='%s' AND file_name='%s'", $tableName, $this->imageId, $fileName));
        return current($imageProxy);
    }

    public function wasGenereatedUsingSameApiKey($fileName)
    {
        $imageProxy = $this->getImageData($fileName);
        if (isset($imageProxy->api_key) && $imageProxy->api_key === SmartFrameOptionProviderFactory::create()->getApiKey()) {
            return true;
        }
        return false;
    }

    public function haveHashedId($fileName)
    {
        $imageProxy = $this->getImageData($fileName);

        if (!$imageProxy) {
            return false;
        } else {
            return true;
        }
    }

    public function buildGenerationData($hashedId, $fileName)
    {
        global $wpdb;

        $tableName = $wpdb->prefix . 'smartframe_image';
        $imageProxy = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE hashed_id='%s'", $tableName, $hashedId));
        $post = get_post($imageProxy[0]->image_id);
        $postMeta = get_post_meta($imageProxy[0]->image_id);
        $imageMetadata = unserialize(current($postMeta['_wp_attachment_metadata']));
        $thumbnail = wp_upload_dir()['baseurl'] . '/' . dirname($imageMetadata['file']) . '/' . $imageMetadata['sizes']['medium']['file'];
        $originalUrl = wp_upload_dir()['baseurl'] . '/' . $postMeta['_wp_attached_file'][0];
//        wp_upload_dir()['basedir'];
//        wp_upload_dir()['baseurl'];

//        $fileName = substr($originalUrl, strrpos($originalUrl, '/') + 1);

        $json = [
            'width' => $imageMetadata['width'],
            'height' => $imageMetadata['height'],
            'path' => $post->guid,
            'thumb_url' => $thumbnail,
            'original_url' => $originalUrl,
            'file_name' => $fileName,
            'size' => filesize(wp_get_upload_dir()['basedir'] . '/' . $postMeta['_wp_attached_file'][0]),
            'api_key' => SmartFrameOptionProviderFactory::create()->getApiKey(),
            'metadata' => serialize($this->getImageMetadata()),
        ];

        $wpdb->update($tableName, $json, ['id' => $imageProxy[0]->id]);
        return $json;
    }

    private function getImageSize($src)
    {
        return @get_headers(preg_replace('/localhost:8080/', 'localhost', $src), 1)['Content-Length'];
    }

    public function getGeneratedHashesForImage()
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'smartframe_image';
        return $wpdb->get_results(sprintf("SELECT * FROM %s WHERE image_id='%s'", $tableName, $this->imageId));
    }

    public function getImageMetadata()
    {
        $result ['caption'] = wp_get_attachment_caption($this->imageId);
        $result['title'] = get_the_title($this->imageId);
        $meta = get_post_meta($this->imageId, '', true);

        $account = SmartFrameApiFactory::create()->get_profile();
        if ($account->getNewImageMetadataMode() === 'V1') {
            $result['description'] = $result['caption'];
            unset($result['caption']);
        }
        if (isset($meta['_wp_attachment_image_alt'][0])) {
            $result['alt'] = $meta['_wp_attachment_image_alt'][0];
        }

        return $result;
    }

}