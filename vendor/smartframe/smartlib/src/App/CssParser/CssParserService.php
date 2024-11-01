<?php

namespace SmartFrameLib\App\CssParser;

use DOMDocument;

class CssParserService
{
    public function replaceImgTagsWithSmartframe($html)
    {
        $name = get_option('smartframe-pregenerator-css');
        $cacheTime = get_option('smartframe-pregenerator-css-cache');
        $dom = new DOMDocument();
        $dom->loadHTML($html, 8192);
        if ($cacheTime < time()) {
            $cssLinksArray = [];

            $domcss = $dom->getElementsByTagName('link');
            $cssNew = '';
            foreach ($domcss as $links) {
                if (strtolower($links->getAttribute('rel')) === 'stylesheet') {
                    $cssLinksArray[] = $links->getAttribute('href');
                }

                foreach ($cssLinksArray as $key => $value) {
                    $cssNew .= preg_replace('/img/', 'smart-frame', file_get_contents(preg_replace('/localhost:8080/', 'localhost', $value)));
                }
            }
            $name = 'public/css/pregenerated/' . 'pregenerated.css';
            $fileName = SMARTFRAME_PLUGIN_DIR . $name;
            $stream = fopen($fileName, 'wb+');
            ftruncate($stream, 0);
            fwrite($stream, $cssNew);
            fclose($stream);

            update_option('smartframe-pregenerator-css-cache', time() + 300);
            update_option('smartframe-pregenerator-css', $name);
        }
        $link = $dom->createElement('link');
        $link->setAttribute('rel', 'stylesheet');
        $link->setAttribute('href', SMARTFRAME_PLUGIN_URL . $name);
        if ($dom->getElementsByTagName('head')->item(0) !== null) {
            $dom->getElementsByTagName('head')->item(0)->appendChild($link);
        }

        return $dom->saveHTML();
    }
}
