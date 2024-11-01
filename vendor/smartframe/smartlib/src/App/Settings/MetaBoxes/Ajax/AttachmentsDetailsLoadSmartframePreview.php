<?php

namespace SmartFrameLib\App\Settings\MetaBoxes\Ajax;
if (!defined('ABSPATH')) exit;

use GuzzleHttp\Exception\GuzzleException;
use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\Providers\SmartFrameImageProvider;
use SmartFrameLib\View\ViewRenderFactory;
use WP_Query;

class AttachmentsDetailsLoadSmartframePreview
{

    public function loadSmartFrame()
    {
        $imageId = sanitize_text_field($_GET['imageId']);

        if (empty($imageId)) {
            $imageId = $this->getFirstJpg();
        }

        try {
            $smartframeImageProvider = new SmartFrameImageProvider($imageId);
        } catch (GuzzleException $e) {
            //log some error or notify user about that
        }
        $data = [
            'theme' => $smartframeImageProvider->getTheme(),
            'imageId' => $smartframeImageProvider->generateHashedId(),
            'id' => 'smartframe-attachment',
            'class' => 'smartframe-attachment-preview',
            'style' => '',
        ];
        $template = ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/inputs/smartframe-frame.php', $data)->render();
        wp_send_json(['template' => $template], 200);
    }

    private function getFirstJpg()
    {
        $query_images_args = [
            'post_type' => 'attachment',
            'post_mime_type' => 'image/jpeg',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
        ];

        $query_images = new WP_Query($query_images_args);

        $images = [];
        foreach ($query_images->posts as $image) {
            $images[] = wp_get_attachment_url($image->ID);
        }

        return current($query_images->posts)->ID;
    }

}