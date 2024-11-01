<?php

namespace SmartFrameLib\App\MediaLibrary;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\App\Providers\SmartFrameImageProvider;

class MediaLibraryManager
{

    public static function create()
    {
        return new self();
    }

    public function loadHooks()
    {
        add_filter('wp_save_image_editor_file', [$this, 'save_image_editor_file'], 100, 5);
    }

    public function save_image_editor_file($saved, $filename, $image, $mime_type, $post_id)
    {
        $imageProvider = new SmartFrameImageProvider($post_id);
        $imageProvider->generateHashedId(get_post($post_id)->guid);
        $save_attempt = $image->save($filename, $mime_type);
        wp_generate_attachment_metadata($post_id, $filename);

//      SmartFrameApiFactory::create()->delete_image($post_id);
//      SmartFrameApiFactory::create()->encode_image($post_id, $filename);

        SmartFrameApiFactory::create()->update_image($post_id, $filename);

        if (null !== $saved)
            return $saved;

        return $save_attempt;
    }

//

    function get_image_sizes()
    {
        global $_wp_additional_image_sizes;

        $sizes = [];

        foreach (get_intermediate_image_sizes() as $_size) {
            if (in_array($_size, ['thumbnail', 'medium', 'medium_large', 'large'])) {
                $sizes[$_size]['width'] = get_option("{$_size}_size_w");
                $sizes[$_size]['height'] = get_option("{$_size}_size_h");
                $sizes[$_size]['crop'] = (bool)get_option("{$_size}_crop");
            } else if (isset($_wp_additional_image_sizes[$_size])) {
                $sizes[$_size] = [
                    'width' => $_wp_additional_image_sizes[$_size]['width'],
                    'height' => $_wp_additional_image_sizes[$_size]['height'],
                    'crop' => $_wp_additional_image_sizes[$_size]['crop'],
                ];
            }
        }

        return $sizes;
    }
}