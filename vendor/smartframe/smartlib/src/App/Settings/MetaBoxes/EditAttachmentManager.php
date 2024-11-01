<?php

namespace SmartFrameLib\App\Settings\MetaBoxes;
if (!defined('ABSPATH')) exit;

use DOMDocument;
use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\Providers\SmartFrameImageProvider;
use SmartFrameLib\App\SmartFramePlugin;
use SmartFrameLib\Converters\StringBoolean;
use SmartFrameLib\Loger\FileLogger;

class EditAttachmentManager
{

    private $optionProvider;

    public function __construct()
    {
        $this->optionProvider = SmartFrameOptionProviderFactory::create();
    }

    public static function create()
    {
        return new self();
    }

    public function register()
    {
//        add_action('add_attachment', [$this, 'add_attachment'], 100);
        // Register action when attachment thumbnails saved
        add_action('delete_attachment', [$this, 'delete_attachment']);
        add_action('edit_attachment', [$this, 'edit_attachment']);
//        add_filter('wp_handle_upload_prefilter', [$this, 'wp_rename_large_images'], 1, 1);

        //more

//        add_filter('image_send_to_editor', [$this, 'rudr_custom_html_template'], 1, 8);

//        MediaLibraryFields::create()->register();
    }

//Check to see if function name is unique
    public function wp_rename_large_images($file)
    {
        //Get image size
        $img = getimagesize($file['tmp_name']);
        $file['name'] = str_replace('-', '_', $file['name']);
        return $file;
    }

    public function add_attachment($attachment_ID)
    {
        $media = get_post($attachment_ID);
        $profile = SmartFrameApiFactory::create()->get_profile();
        $image = get_attached_file($attachment_ID);
    }

    public function edit_attachment($attachment_ID)
    {
        $imageModel = new \stdClass();
        $imageModel->metaData = new \stdClass();

        if (isset($_POST['post_title'])) {
            $imageModel->metaData->title = $_POST['post_title'];
        }
        if (isset($_POST['_wp_attachment_image_alt'])) {
            $imageModel->metaData->alt = $_POST['_wp_attachment_image_alt'];
        }
        if (isset($_POST['excerpt'])) {
            $imageModel->metaData->description = $_POST['excerpt'];
        }

        if (isset($_POST['changes']['caption'])) {
            $imageModel->metaData->description = $_POST['changes']['caption'];
        }
        if (isset($_POST['changes']['alt'])) {
            $imageModel->metaData->alt = $_POST['changes']['alt'];
        }
        if (isset($_POST['changes']['title'])) {
            $imageModel->metaData->title = $_POST['changes']['title'];
        }

        $imageProvider = new SmartFrameImageProvider($attachment_ID);
        $result = $imageProvider->getGeneratedHashesForImage();

        try {
            foreach ($result as $image) {
                $imageModel->name = $image->hashed_id;
                SmartFrameApiFactory::create()->updateImageMetadata($imageModel);
            }
        } catch (\Exception $e) {
            FileLogger::log($e->getMessage(), 'EditAttachmentManager.log');
        }
    }

    public function delete_attachment($attachment_ID)
    {
        global $wpdb;
        $tableName = $wpdb->prefix . 'smartframe_image';
        $images = $wpdb->get_results(sprintf("SELECT * FROM %s WHERE image_id='%s'", $tableName, $attachment_ID));

        foreach ($images as $image) {
            if (SmartFrameApiFactory::create()->delete_image($image->hashed_id)) {
                // delete attachment custom fields
                $wpdb->delete($tableName, ['hashed_id' => $image->hashed_id]);
            }
        }
    }

    function rudr_custom_html_template($html, $id, $caption, $title, $align, $url, $size, $alt)
    {
        list($img_src, $width, $height) = image_downsize($id, $size);
        $hwstring = image_hwstring($width, $height);

        $useSmartframe = SmartFrameOptionProviderFactory::create()->getAttachmentUseSmartFrame($id, true);
        $template = SmartFrameOptionProviderFactory::create()->getThemeFromAttachment($id);

        $dom = $doc = new DOMDocument();
        $dom->loadHTML($html);

        $img = $dom->getElementsByTagName('img')->item(0);
        $img->setAttribute('data-smartframe-enabled', $useSmartframe);
        $img->setAttribute('data-smartframe-theme', $template);

//    delete_post_meta($id, 'sfm_smartframe_use');
//    delete_post_meta($id, 'sfm_use_theme');

        $dom->removeChild($dom->doctype);
        $content = $dom->saveHTML();
// remove <html><body></body></html>
        $content = str_replace('<html><body>', '', $content);
        $content = str_replace('</body></html>', '', $content);

        return $content; // the result HTML
    }

}