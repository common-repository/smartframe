<?php

namespace SmartFrameLib\App\Theme;
if (!defined('ABSPATH')) exit;

use SmartFrameLib\Api\SmartFrameApiFactory;

class ThemeProvider
{

    private static $instance = null;
    private $themes;
    /**
     * @var \SmartFrameLib\Api\LazyLoadingSmartFrameApi
     */
    private $api;

    public function __construct()
    {
        $this->api = SmartFrameApiFactory::create();
        $this->themes = $this->api->getThemes();
        if ($this->themes === null) {
            $this->themes = [];
        }
    }

    /**
     * @return ThemeProvider
     */
    public static function create()
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @return array
     */
    public function provideThemesIds()
    {
        return array_map(function ($value) {
            return $value->slug;
        }, $this->themes);
    }

    /**
     * @return array
     */
    public function provideThemes()
    {
        return $this->themes;
    }

    /**
     * @param bool $removeDefaultTheme
     * @return array
     */
    public function provideKeyValueTheme()
    {
        $result = [];
        foreach ($this->themes as $key => $theme) {
            if ($theme->defaultTheme) {
                $result = [$theme->slug => $theme->name . ' (default)'] + $result;
                continue;
            }
            $result[$theme->slug] = $theme->name;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function provideDefaultTheme()
    {
        $default = current(array_filter($this->themes, function ($element) {
            if ($element->defaultTheme) {
                return $element;
            }
        }));

        if (empty($default)) {
            return [];
        }
        return [$default->slug => $default->name . ' (default)'];
    }

}