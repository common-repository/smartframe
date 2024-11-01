<?php

namespace SmartFrameLib\App\Migrations\VersionMigrator;

use SmartFrameLib\App\Providers\SmartFrameImageProvider;

if (!defined('ABSPATH')) exit;

class ImagesIdFromMetaDataToSmartFrameTable
{
    public function migrate()
    {
        global $wpdb;
        $convertedImages = $wpdb->get_results("SELECT * FROM `wp_postmeta` WHERE meta_key='pixelrights_smartframe_id' and meta_value <>'';");

        foreach ($convertedImages as $postMeta) {
            $imageProvider = new SmartFrameImageProvider($postMeta->post_id);
            $imageProvider->generateDataForExistingImages(get_post($postMeta->post_id)->guid, $postMeta->meta_value);
            delete_post_meta($postMeta->post_id, 'pixelrights_smartframe_id');
        }
    }

}