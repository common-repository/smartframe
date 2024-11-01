<?php

namespace SmartFrameLib\Widget;
 if ( ! defined( 'ABSPATH' ) ) exit;

abstract class AbstractWidget extends \WP_Widget implements \SmartFrameLib\Widget\Interfaces\WidgetInterface {

    protected $viewRenderer = null;

    public function form($instance) {
        throw new \Exception('This method must be overwritten');
    }

    public function update($newInstance, $oldInstance) {
        throw new \Exception('This method must be overwritten');
    }

    public function widget($arg, $instance) {
        throw new \Exception('This method must be overwritten');
    }

    protected function renderView($file, array $vars) {
        if ($this->viewRenderer === null) {
            $this->viewRenderer = new \SmartFrameLib\View\ViewRendererClass($file, $vars);
        }
        return $this->viewRenderer->render();
    }

}
