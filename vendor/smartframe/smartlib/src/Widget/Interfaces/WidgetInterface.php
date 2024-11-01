<?php

namespace SmartFrameLib\Widget\Interfaces;
 if ( ! defined( 'ABSPATH' ) ) exit;

interface WidgetInterface {

     public function form($instance);

     public function update($newInstance, $oldInstance);

     public function widget($arg, $instance);
}
