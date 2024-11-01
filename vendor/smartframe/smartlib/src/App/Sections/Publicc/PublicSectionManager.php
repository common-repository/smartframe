<?php

namespace SmartFrameLib\App\Sections\Publicc;
if (!defined('ABSPATH')) exit;

use DOMDocument;
use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\CssParser\CssParserService;
use SmartFrameLib\App\Providers\SmartFrameImageProvider;
use SmartFrameLib\App\RestActions\RequestChecker;
use SmartFrameLib\App\SmartFramePlugin;
use SmartFrameLib\Loger\FileLogger;

class PublicSectionManager
{

    /**
     * @var RequestChecker
     */
    private $requestChecker;

    public function __construct()
    {
        $this->requestChecker = new RequestChecker();
    }

    public function loadHooks()
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_action('after_setup_theme', [$this, 'smartframe_buffer_start']);
        add_action('shutdown', [$this, 'smartframe_buffer_end']);
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style(SmartFramePlugin::provideName(), SMARTFRAME_PLUGIN_URL . 'public/css/smartframe-public.css', [], SmartFramePlugin::provideVersion(), 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        if (!empty(SmartFrameOptionProviderFactory::create()->getWebComponentScriptUrl())) {
            wp_enqueue_script(SmartFramePlugin::provideName() . '-script', SmartFrameOptionProviderFactory::create()->getWebComponentScriptUrl(), [], SmartFramePlugin::provideVersion(), false);
        }

        add_thickbox();

        // localize and load script
        wp_register_script(SmartFramePlugin::provideName(), SMARTFRAME_PLUGIN_URL . '/public/js/smartframe-public.js', ['jquery'], SmartFramePlugin::provideVersion(), true);
        $translation_array = [
            'minwidth' => SmartFrameOptionProviderFactory::create()->getMinWidth(),
            'minheight' => SmartFrameOptionProviderFactory::create()->getMinHeight(),
            'ajax_url' => admin_url('admin-ajax.php'),
        ];
        wp_localize_script(SmartFramePlugin::provideName(), 'smartframe', $translation_array);
        wp_enqueue_script(SmartFramePlugin::provideName());
    }

    /**
     * Register after_setup_theme action.
     * Start bufering wordpress HTML and register callback
     *
     * @since    1.0.0
     */
    public function smartframe_buffer_start()
    {
        if (!is_admin() && !$this->requestChecker->is_rest()) {
            ob_start(function ($buffer) {
                return $this->convert_img_to_smartframe($buffer);
            });
        }
    }

    public function convert_img_to_smartframe($buffer)
    {
//        $buffer = mb_convert_encoding($buffer, 'HTML-ENTITIES', "UTF-8");
//        $buffer = file_get_contents(SMARTFRAME_PLUGIN_DIR . 'vendor/smartframe/smartlib/src/App/Sections/Publicc/test.html');
//        return $buffer;
        try {
            if (preg_match('/(<!doctype html>|<!--WPFC_)/i', $buffer) === 1) {
                // Create a new istance of DOMDocument
                libxml_use_internal_errors(true);
                $doc = new DOMDocument();

                $js = [];
                $content = $buffer;
                preg_match_all('/<script[\s\S]*?>[\s\S]*?<\/[\s\S]*?script>/', $content, $matches);
                foreach ($matches[0] as $key => $value) {
                    $js['<temp-script-remover>' . $key] = $value;
                }

                $repNumber = 0;

                $content = preg_replace_callback('/<script[\s\S]*?>[\s\S]*?<\/[\s\S]*?script>/', function ($text) use (&$repNumber) {
                    return '<temp-script-remover>' . $repNumber++ . '';
                }, $content);

                $doc->loadHTML($content, 8192);

                $doc = $this->convertFiguresToSmartFrame($doc);
                libxml_use_internal_errors(false);

                $html = preg_replace_callback('/<temp-script-remover>+(\d+)/', function ($text) use ($js) {
                    return $js[$text[0]];
                }, $doc->saveHTML());
                FileLogger::log($html, 'after-pc-doc.log');
                return $html;
            }
        } catch (\Exception $e) {
            FileLogger::log($e->getMessage(), 'public-section.log');
        }

        return $buffer;
    }

    private function convertFiguresToSmartFrame($doc)
    {
        $loadedOptions = $this->prepareConfig($doc);

        if (!$loadedOptions ['useSmartframe']) {
            return $doc;
        }

        $imgLength = 0;
        $imgs = $doc ? $doc->getElementsByTagName('img') : [];

        while ($imgLength !== $imgs->length) {
            $imgLength = $imgs->length;
            foreach ($imgs as $img) {
                if (get_option('smartframe_privacy_policy') === false) {
                    continue;
                }
                if (empty(SmartFrameOptionProviderFactory::create()->getApiKey())) continue;
                if (preg_match('/(\.jpg|\.jpeg)/', $img->getAttribute('src')) === 0) continue;
                $attachment_id = $this->attachemtId($img);
                if (empty($attachment_id) || get_post($attachment_id) === null) {
                    continue;
                }

                if (!$this->canDisplaySmartFrame($img, $loadedOptions)) {
                    continue;
                }

                $smartframe = $doc->createElement('smart-frame');

                $width = $img->getAttribute('width');
                $height = $img->getAttribute('height');
//                if (empty($width) && empty($height)) {
//                    list($width, $height, $type, $attr) = @getimagesize(preg_replace('/localhost:8080/', 'localhost', $img->getAttribute('src')));
//                }

                $imageProvider = new SmartFrameImageProvider($attachment_id);

                if (!$imageProvider->canBeDisplayed($img->getAttribute('src'))) {
                    continue;
                }
                $style = $img->getAttribute('style');

                if ($this->scanNodes($img, 'is-cropped', 7)) {
                    $style .= '--sf-image-size: cover;--sf-image-position:center;height:100%;';
                    $style .= 'width:100%;';
                }
                
                $smartframe->setAttribute('style', $style);

                if (isset($_GET['preview_id'])) {
                    $smartframe->setAttribute('preview', '');
                }
                $smartframe->setAttribute('class', $img->getAttribute('class') . ' smart-frame');
                $smartframe->setAttribute('id', 'smartframe_' . $attachment_id);
                $smartframe->setAttribute('image-id', $imageProvider->generateHashedId($img->getAttribute('src')));

                if ($loadedOptions ['theme']) {
                    $smartframe->setAttribute('theme', $loadedOptions ['theme']);
                }
                if (strtolower($img->parentNode->tagName) === 'a' && preg_match('/(\.jpg|\.jpeg)/', $img->parentNode->getAttribute('href'))) {
                    $a = $img->parentNode;
                    $img->parentNode->parentNode->replaceChild($smartframe, $a);
                } else if (strtolower($img->parentNode->tagName) === 'a') {
                    $smartframe->setAttribute('class', $img->getAttribute('class') . ' smart-frame hide-overlay');
                    $img->parentNode->replaceChild($smartframe, $img);
                } else {
                    $img->parentNode->replaceChild($smartframe, $img);
                }
//                $img->parentNode->replaceChild($smartframe, $img);
            }
        }

        return $doc;
    }

    /**
     * @param $doc
     * @param $loadedOptions
     * @return mixed
     */
    private function prepareConfig($doc)
    {
        $loadedOptions = [];
        if (isset($_GET['theme']) && is_user_logged_in()) {
            $loadedOptions ['theme'] = $_GET['theme'];
            $loadedOptions ['useSmartframe'] = $_GET['useSmartframe'] === 'yes';
            $loadedOptions ['disableCss'] = $_GET['disableCss'];
            $loadedOptions ['enabledCssClassList'] = $_GET['enabledCssClassList'];
            $loadedOptions ['disableCssClassList'] = $_GET['disableCssClassList'];
            foreach ($doc->getElementsByTagName('a') as $link) {
                if (!strpos($link->getAttribute('href'), 'wp-admin')) {
                    $link->setAttribute('href', $this->buildUrl($link->getAttribute('href'), $_GET));
                }
            }
        } else {
            $loadedOptions ['theme'] = SmartFrameOptionProviderFactory::create()->getThemeForSmartframe();
            $loadedOptions ['disableCss'] = SmartFrameOptionProviderFactory::create()->getDisabledCssClasses();
            $loadedOptions ['disableCssClassList'] = SmartFrameOptionProviderFactory::create()->getDisabledCssClassesList();
            $loadedOptions ['enabledCssClassList'] = SmartFrameOptionProviderFactory::create()->getEnabledCssClassesList();
            $loadedOptions ['useSmartframe'] = SmartFrameOptionProviderFactory::create()->getUseSmartFrame();
        }

        $loadedOptions ['disableCssClassList'] = trim($loadedOptions ['disableCssClassList'], '.,\s');
        $loadedOptions ['disableCssClassList'] = preg_replace('/\.|\,/', ' ', $loadedOptions ['disableCssClassList']);

        return $loadedOptions;
    }

    private function buildUrl($url, $data)
    {
        $query = http_build_query($data);
        $parsedUrl = parse_url($url);
        if ($parsedUrl['path'] == null) {
            $url .= '/';
        }
        $separator = ($parsedUrl['query'] == null) ? '?' : '&';
        $url .= $separator . $query;
        return $url;
    }

    /**
     * @param $img
     * @return string
     */
    private function attachemtId($img)
    {
        $imgClass = $img->getAttribute('class');
        $attachment_id_from_class = $this->get_attachment_id_from_class($imgClass);
        $src = $img->getAttribute('src');
        $attachment_id = (!$attachment_id_from_class) ? $this->get_attachment_id_from_url($src) : (int)$attachment_id_from_class;
        return $attachment_id;
    }

    public function get_attachment_id_from_class($imgClass = '')
    {
        $attachment_id = false;

        // If there is no class, return.
        if ('' == $imgClass)
            return;

        $classes = explode(' ', $imgClass);
        foreach ($classes as $class) {
            $pos = strstr($class, 'wp-image-');
            if ($pos) {
                $attachment_id = intval(substr($class, $pos + 9));
            }
        }

        return $attachment_id;
    }

    /**
     * Get attachment ID from attachment url
     *
     * @param string $attachment_url
     * @since    1.0.0
     */
    public function get_attachment_id_from_url($attachment_url = '')
    {
        global $wpdb;
        $attachment_id = false;

        // If there is no url, return.
        if ('' == $attachment_url)
            return;

        // Get the upload directory paths
        $upload_dir_paths = wp_upload_dir();

        // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
        if (false !== strpos($attachment_url, $upload_dir_paths['baseurl'])) {
            // If this is the URL of an auto-generated thumbnail, get the URL of the original image
            $attachment_url_with_host = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url);

            // Remove the upload path base directory from the attachment URL
            $attachment_url = str_replace($upload_dir_paths['baseurl'] . '/', '', $attachment_url_with_host);

            // Finally, run a custom database query to get the attachment ID from the modified attachment URL
            $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url));
            if ($attachment_id === null) {
                $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT wpposts.ID FROM $wpdb->posts wpposts where wpposts.guid = %s", $attachment_url_with_host));
            }
        }
        return $attachment_id;
    }

    private function canDisplaySmartFrame($img, $loadedOptions)
    {
        if ($loadedOptions ['disableCss'] === 'exclude_images') {
            if (!empty($loadedOptions ['disableCssClassList'])) {
                $pattern = sprintf('/%s/', preg_replace('/\s+/', '|', str_replace(['.', ','], ' ', trim($loadedOptions ['disableCssClassList'], '|,. '))));
            } else {
                return false;
            }
            $figure = $img->parentNode;
            $imgClass = $img->getAttribute('class');

            if (preg_match($pattern, $imgClass) !== 0) {
                return false;
            }

            if ($figure->tagName === 'a') {
                $figure = $figure->parentNode;
            }

            if ($figure->tagName === 'figure') {
                $figureClass = $figure->getAttribute('class');
                if (preg_match($pattern, $figureClass) !== 0) {
                    return false;
                }

                $figureClass = $figure->parentNode->getAttribute('class');
                if (preg_match($pattern, $figureClass) !== 0) {
                    return false;
                }
            }

            return true;
        }

        if ($loadedOptions ['disableCss'] === 'include_images') {
            if (!empty($loadedOptions ['enabledCssClassList'])) {
                $pattern = sprintf('/%s/', preg_replace('/\s+/', '|', str_replace(['.', ','], ' ', trim($loadedOptions ['enabledCssClassList'], '|,. '))));
            } else {
                return true;
            }
            $figure = $img->parentNode;
            $imgClass = $img->getAttribute('class');

            if (preg_match($pattern, $imgClass) !== 0) {
                return true;
            }

            if ($figure->tagName === 'a') {
                $figure = $figure->parentNode;
            }

            if ($figure->tagName === 'figure') {
                $figureClass = $figure->getAttribute('class');
                if (preg_match($pattern, $figureClass) !== 0) {
                    return true;
                }

                $figureClass = $figure->parentNode->getAttribute('class');
                if (preg_match($pattern, $figureClass) !== 0) {
                    return true;
                }
            }

            return false;
        }

        if ($loadedOptions ['disableCss'] === 'all_images' || empty($loadedOptions ['disableCss'])) {
            return true;
        }

        return true;
    }

    public function scanNodes($node, $class, $depth)
    {
        if ($node === null) {
            return false;
        }
        if ($depth <= 0) {
            return false;
        }
        if (strpos($node->getAttribute('class'), $class) !== false) {
            return true;
        }

        return $this->scanNodes($node->parentNode, $class, --$depth);
    }

    public function smartframe_buffer_end()
    {
        if (!is_admin() && ob_get_length() !== false) {
            ob_end_flush();
        }
    }

    public function removeHrefElementFromOuterNode($img)
    {
        $img->parentNode->setAttribute('href', '#');
    }

    private function prepareCss($buffer)
    {
        return (new CssParserService())->replaceImgTagsWithSmartframe($buffer);
    }
}