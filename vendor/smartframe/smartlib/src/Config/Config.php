<?php

namespace SmartFrameLib\Config;
 if ( ! defined( 'ABSPATH' ) ) exit;

class Config
{
    private static $instance = null;
    protected $config = [];

    /**
     * Singleton.
     */
    private function __construct()
    {
    }

    /**
     *
     * @param string $key
     * @param string $data
     * @return Config
     * @throws \Exception
     */
    public function addConfig($key, $data)
    {
        if (isset($this->config[$key])) {
            throw new \Exception(sprintf('Config Key %s already exists.', $key));
        }
        $this->config[$key] = $data;
        return $this;
    }

    /**
     *
     * @param string $key
     * @param type $data
     * @return Config
     */
    public function replace($key, $data)
    {
        $this->config[$key] = $data;
        return $this;
    }

    /**
     *
     * @param string $key
     * @return string
     * @throws \Exception
     */
    public function getConfig($key)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        throw new \Exception(sprintf('No config exists with key %s', $key));
    }

    public function hasKey($key)
    {
        if (isset($this->config[$key])) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return Config
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}