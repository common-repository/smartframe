<?php

namespace SmartFrameLib\Listener;
 if ( ! defined( 'ABSPATH' ) ) exit;

abstract class Listener
{
    abstract public function listen();
}