<?php

namespace SmartFrameLib\Plugin;
 if ( ! defined( 'ABSPATH' ) ) exit;

abstract class PluginManager implements PluginInterface
{
    private $loaders = array();

    public function addLoader(\SmartFrameLib\Interfaces\LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
        return $this;
    }

    private function loadLoaders()
    {
        foreach ($this->loaders as $loader) {
            $loader->run();
        }
    }

    public function run()
    {
        $this->loadLoaders();
    }
}