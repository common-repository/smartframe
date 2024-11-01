<?php

namespace SmartFrameLib\App\Settings\MetaBoxes\Ajax;
if (!defined('ABSPATH')) exit;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use SmartFrameLib\Api\LazyLoadingSmartFrameApi;
use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\Providers\SmartFrameImageProvider;

class MediaLibraryFieldsLoader
{
    public function getCaptionForAttachment()
    {
        $smartframeImageId = SmartFrameOptionProviderFactory::create()->getGeneratedAttachmentIdBySmartFrame((int)$_GET['imageId']);
        if (empty($smartframeImageId)) {
            $dataJson = ['smartframeCaption' => ''];
            wp_send_json($dataJson);
        }
        try {
            $response = SmartFrameApiFactory::create()->getImage($smartframeImageId);
            $data = json_decode($response->getBody()->getContents());
            $dataJson = ['smartframeCaption' => $this->getProperDesc($data)];
            wp_send_json($dataJson);
        } catch (ClientException $e) {
        }
    }

    public function saveCaptionForAttachment()
    {
        (new SmartFrameImageProvider((int)$_GET['imageId']))->getSmartFrameImageId();
        $imageModel = new \stdClass();
        $imageModel->metaData->description = wp_unslash($_GET['caption']);
        $imageModel->name = SmartFrameOptionProviderFactory::create()->getGeneratedAttachmentIdBySmartFrame((int)$_GET['imageId']);
        SmartFrameOptionProviderFactory::create()->setCaptionForAttachment((int)$_GET['imageId'], $_GET['caption']);

        try {
            $response = SmartFrameApiFactory::create()->updateImageMetadata($imageModel);
            $data = json_decode($response->getBody()->getContents());
            $dataJson = ['smartframeCaption' => $this->getProperDesc($data)];
            wp_send_json($dataJson);
        } catch (ClientException $e) {
        }
    }

    public function previewCaptionForAttachment()
    {
        $imageModel = new \stdClass();
        $imageModel->metaData->description = $_GET['caption'];
        $imageModel->name = SmartFrameOptionProviderFactory::create()->getGeneratedAttachmentIdBySmartFrame((int)$_GET['imageId']);
        try {
            $response = SmartFrameApiFactory::create()->updateImageMetadata($imageModel);
            $data = json_decode($response->getBody()->getContents());
            $dataJson = ['smartframeCaption' => $this->getProperDesc($data)];
            wp_send_json($dataJson);
        } catch (ClientException $e) {
        }
    }

    public function revertCaptionForAttachment()
    {
        $imageModel = new \stdClass();
        $imageModel->metaData->description = SmartFrameOptionProviderFactory::create()->getCaptionFromAttachment((int)$_GET['imageId']);
        $imageModel->name = SmartFrameOptionProviderFactory::create()->getGeneratedAttachmentIdBySmartFrame((int)$_GET['imageId']);
        try {
            $response = SmartFrameApiFactory::create()->updateImageMetadata($imageModel);
            $data = json_decode($response->getBody()->getContents());
            $dataJson = ['smartframeCaption' => $this->getProperDesc($data)];
            wp_send_json($dataJson);
        } catch (ClientException $e) {
        }
    }

    private function getProperDesc($imageModel)
    {
        $account = SmartFrameApiFactory::create()->get_profile();
        if ($account->getNewImageMetadataMode() === 'V2') {
            return $imageModel->metadata->caption;
        }
        return $imageModel->metadata->description;
    }

}