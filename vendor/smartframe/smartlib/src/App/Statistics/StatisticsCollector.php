<?php

namespace SmartFrameLib\App\Statistics;

use Fragen\GitHub_Updater\Theme;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\App\Theme\ThemeProvider;
use SmartFrameLib\Loger\FileLogger;

class StatisticsCollector
{

    /**
     * @var \SmartFrameLib\Api\LazyLoadingSmartFrameApi|null
     */
    private $api;
    /**
     * @var \SmartFrameLib\Api\SmartFrameOptionProvider
     */
    private $options;

    public function __construct()
    {
        $this->api = \SmartFrameLib\Api\SmartFrameApiFactory::create(false);
        $this->options = SmartFrameOptionProviderFactory::create();
    }

    public function sendPluginVersion($version)
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log(sprintf('sendPluginVersion API KEY EMPTY:%s', $version), 'StatistisCollector.txt');
            return;
        }
        FileLogger::log(sprintf('sendPluginVersion:%s', $version), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['statusVersion' => $version]);
    }

    public function sendWordpressVersion($version)
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendWordpressVersion API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }
        FileLogger::log(sprintf('sendWordpressVersion:%s', $version), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['wordPressVersion' => $version]);
    }

    public function sendPluginDeactivationTime($time)
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendPluginDeactivationTime API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }
        FileLogger::log(sprintf('sendPluginDeactivationTime:%s', $time), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['deactivationTime' => $time]);
    }

    public function sendPluginActivationTime($time)
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendPluginActivationTime API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }
        FileLogger::log(sprintf('sendPluginActivationTime:%s', $time), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['activationTime' => $time]);
    }


    public function sendPluginStatus($status)
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendPluginStatus API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }
        FileLogger::log(sprintf('sendPluginStatus:%s', $status), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['pluginStatus' => $status]);
    }

    public function sendCurrentImageCount()
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendCurrentImageCount API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }

        $data = (array)wp_count_attachments($mime_type = 'image');
        FileLogger::log(sprintf('sendCurrentImageCount:%s', array_sum($data)), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['amountImagesInMediaLibrary' => (string)array_sum($data)]);
    }

    public function sendCurrentSmartFrameThem($theme)
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendCurrentSmartFrameThem API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }
        if ($theme === false || empty($theme)) {
            $theme = key(ThemeProvider::create()->provideDefaultTheme());
        }
        FileLogger::log(sprintf('sendCurrentSmartFrameThem:%s', (string)$theme), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['currentSmartFrameTheme' => $theme]);
    }

    public function sendCurrentWordpressTheme($theme)
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendCurrentWordpressTheme API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }
        FileLogger::log(sprintf('sendCurrentWordpressTheme:%s', $theme), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['currentWordpressTheme' => $theme]);
    }

    public function sendConversionStatus($status)
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendConversionStatus API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }
        FileLogger::log(sprintf('sendConversionStatus:%s', $status), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['conversionStatus' => $status === true ? 'Active' : 'Not active']);
    }

    public function sendAmountOfPagesAndPosts()
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendAmountOfPagesAndPosts API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }
        FileLogger::log(sprintf('sendAmountOfPagesAndPosts:%s', wp_count_posts()->publish + wp_count_posts('page')->publish), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['amountOfPagesAndPosts' => (string)(wp_count_posts()->publish + wp_count_posts('page')->publish)]);
    }

    public function sendAmountOfComments()
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendAmountOfComments API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }
        FileLogger::log(sprintf('sendAmountOfComments:%s', count(get_comments())), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['amountOfComments' => (string)count(get_comments())]);
    }

    public function sendPing()
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendPing API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }
        FileLogger::log(sprintf('sendPing:'), 'StatistisCollector.txt');
        $this->api->postStatisticsData(['ping' => (new \DateTime())->format('Y-m-d H:i:s')]);
    }

    public function sendActivePlugins()
    {
        if (empty($this->options->getApiKey())) {
            FileLogger::log('sendActivePlugins API KEY EMPTY:', 'StatistisCollector.txt');
            return;
        }
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        FileLogger::log(sprintf('sendActivePlugins:START'), 'StatistisCollector.txt');
        $apl = get_option('active_plugins');
        $plugins = get_plugins();
        $activated_plugins = [];
        foreach ($apl as $p) {
            if (isset($plugins[$p])) {
                $activated_plugins[] = $plugins[$p]['Name'];
            }
        }
        FileLogger::log(sprintf('sendActivePluginLIST:%s', implode(' | ', $activated_plugins)), 'StatistisCollector.txt');
        FileLogger::log(sprintf('sendActivePlugins:END'), 'StatistisCollector.txt');

        $this->api->postStatisticsData(['activePlugins' => implode(' | ', $activated_plugins)]);
    }

}