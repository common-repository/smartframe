<?php

namespace SmartFrameLib\App\Statistics;

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameOptionProviderFactory;
use SmartFrameLib\Loger\FileLogger;

class CronScheduler
{
    const ACTIVATION_TIME_OPTION = 'smartframe-activation-time';

    /**
     * @var StatisticsCollector
     */
    private $statisticsCollector;

    public function __construct()
    {
        $this->extraCronTime();
        $this->statisticsCollector = new StatisticsCollector();
    }

    public function schedule()
    {
        if (!SmartFrameApiFactory::create()->check_credentials() || get_option('smartframe_privacy_policy') === false) {
            return;
        }
//        wp_clear_scheduled_hook('on24hours');
//        wp_clear_scheduled_hook('on5min');
        if (!wp_next_scheduled('on24hours')) {
            $this->on24hours();
            wp_schedule_event(time() + 30, 'daily', 'on24hours');
        }

        if (!wp_next_scheduled('on5min')) {
            $this->on5min();
            wp_schedule_event(time() + 30, 'every_fife_minute', 'on5min');
        }
//        $this->on24hours();
//        $this->on5min();

        add_action('on24hours', [new CronScheduler(), 'on24hours']);
        add_action('on5min', [new CronScheduler(), 'on5min']);
    }

    public function on24hours()
    {
        FileLogger::log(sprintf('----------CRON JOB ON 24H:%s', (new \DateTime())->format('Y-m-d H:i:s')), 'StatistisCollector.txt');
        global $wp_version;
        $this->statisticsCollector->sendPluginVersion(\SmartFrameLib\App\SmartFramePlugin::$VERSION);
        $this->statisticsCollector->sendWordpressVersion($wp_version);
        $this->statisticsCollector->sendCurrentWordpressTheme(wp_get_theme()->get('Name'));
        $this->statisticsCollector->sendPing();
        $this->statisticsCollector->sendActivePlugins();
        FileLogger::log(sprintf('----------CRON JOB ON 24H:END:'), 'StatistisCollector.txt');
    }

    public function on5min()
    {
        FileLogger::log(sprintf('----------CRON JOB ON 5 MIN:%s', (new \DateTime())->format('Y-m-d H:i:s')), 'StatistisCollector.txt');
        $imageCount = array_sum((array)wp_count_attachments($mime_type = 'image'));
        $amountOfPosts = wp_count_posts()->publish + wp_count_posts('page')->publish;
        $amountOfComments = count(get_comments());
        FileLogger::log(sprintf('$imageCount:%s, $amountOfPosts:%s, $amountOfComments:%s', $imageCount, $amountOfPosts, $amountOfComments), 'StatistisCollector.txt');

        if (get_option('smartframe-current-image-count') === false || (int)get_option('smartframe-current-image-count') !== $imageCount) {
            $this->statisticsCollector->sendCurrentImageCount();
            update_option('smartframe-current-image-count', $imageCount);
        }
        if (get_option('smartframe-current-post-pages-amount') === false || (int)get_option('smartframe-current-post-pages-amount') !== (int)$amountOfPosts) {
            $this->statisticsCollector->sendAmountOfPagesAndPosts();
            update_option('smartframe-current-post-pages-amount', (int)$amountOfPosts);
        }

        if (get_option('smartframe-current-comments-amount') === false || (int)get_option('smartframe-current-comments-amount') !== $amountOfComments) {
            $this->statisticsCollector->sendAmountOfComments();
            update_option('smartframe-current-comments-amount', $amountOfComments);
        }

        if (get_option(self::ACTIVATION_TIME_OPTION) !== false) {
            (new StatisticsCollector())->sendPluginActivationTime(get_option(self::ACTIVATION_TIME_OPTION));
            (new StatisticsCollector())->sendPluginStatus('Active');
            delete_option(self::ACTIVATION_TIME_OPTION);
        }

        FileLogger::log(sprintf('----------CRON JOB ON 5 MIN:END:'), 'StatistisCollector.txt');
    }

    public function extraCronTime()
    {
        add_filter('cron_schedules', function ($schedules) {
            $schedules['every_one_minute'] = [
                'interval' => 60,
                'display' => __('Every 1 Minutes', 'textdomain'),
            ];
            $schedules['every_fife_minute'] = [
                'interval' => 300,
                'display' => __('Every 5 Minutes', 'textdomain'),
            ];
            $schedules['every_teen_minute'] = [
                'interval' => 600,
                'display' => __('Every 10 Minutes', 'textdomain'),
            ];
            return $schedules;
        });
    }

}