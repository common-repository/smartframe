<?php

namespace SmartFrameLib\Plugin;
 if ( ! defined( 'ABSPATH' ) ) exit;

interface PluginInterface
{

    public function onActivate();

    public function onDeactivate();

    public function onDelete();
}