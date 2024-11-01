<?php

namespace SmartFrameLib\App\RestActions;

use SmartFrameLib\Api\SmartFrameOptionProviderFactory;

class RestRouteLoader
{

    public function load()
    {
        add_action('rest_api_init', function () {
            register_rest_route('smartframe/v1', '/images-proxy/(?P<id>[a-z A-Z 0-9\-\_]+)', [
                'methods' => 'GET',
                'callback' => [$this, 'imageProxy'],
            ]);
        });

        add_action('rest_api_init', function () {
            register_rest_route('smartframe/v1', '/images-data/(?P<id>[a-z A-Z 0-9\-\_]+)', [
                'methods' => 'GET',
                'callback' => [$this, 'imageData'],
            ]);
        });
    }

    public function imageProxy($data)
    {
        return [];
    }

    public function imageData($data)
    {
        if (SmartFrameOptionProviderFactory::create()->getWpPluginApiKey() !== $data->get_header('x-wp-auth-header')) {
            return ['validation_errors' => [
                'wpImageKey' => 'Wrong api key',
            ]];
        }

        global $wpdb;

        $tableName = $wpdb->prefix . 'smartframe_image';
        $imageProxy = current($wpdb->get_results(sprintf("SELECT * FROM %s WHERE hashed_id='%s'", $tableName, $data['id'])));
        if (getenv('WORDPRESS_URL') === 'localhost:8080') {
            $json = [
                'width' => $imageProxy->width,
                'height' => $imageProxy->height,
                'file' => $imageProxy->file_name,
                'thumbnailUrl' => str_replace('localhost:8080', '172.18.0.4', $imageProxy->thumb_url),
                'originalUrl' => str_replace('localhost:8080', '172.18.0.4', $imageProxy->original_url),
                'size' => $imageProxy->size,
                'metadata' => unserialize($imageProxy->metadata),
            ];
        } else {
            $json = [
                'width' => $imageProxy->width,
                'height' => $imageProxy->height,
                'file' => $imageProxy->file_name,
                'thumbnailUrl' => $imageProxy->thumb_url,
                'originalUrl' => $imageProxy->original_url,
                'size' => $imageProxy->size,
                'metadata' => unserialize($imageProxy->metadata),
            ];
        }

        return $json;
    }

}