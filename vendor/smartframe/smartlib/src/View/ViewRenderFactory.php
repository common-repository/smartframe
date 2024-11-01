<?php

namespace SmartFrameLib\View;
if (!defined('ABSPATH')) exit;

class ViewRenderFactory
{
    public static function create($template, $args = [])
    {
        return new ViewRendererClass($template, $args);
    }
}