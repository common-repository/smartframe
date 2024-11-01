<?php

namespace SmartFrameLib\App\MenuHandlers;
 if ( ! defined( 'ABSPATH' ) ) exit;

use SmartFrameLib\Api\SmartFrameApiFactory;
use SmartFrameLib\Api\SmartFrameApiInterface;
use SmartFrameLib\Converters\ByteSizeConverter;
use SmartFrameLib\View\ViewRenderFactory;

class DashboardMenuHandler
{
    /**
     * @var SmartFrameApiInterface
     */
    private $apiClient;

    /**
     * DashboardMenuHandler constructor.
     */
    public function __construct()
    {
        //prepare settings fields to display

        $this->apiClient = SmartFrameApiFactory::create();
    }

    /**
     * @return string
     */
    public function display()
    {
        return ViewRenderFactory::create(SMARTFRAME_PLUGIN_DIR . '/admin/partials/settings/dashboard.php', $this->prepareVariablesToRenderView())->display();
    }

    /**
     * @return array
     */
    private function prepareVariablesToRenderView()
    {
        $accountData = $this->apiClient->get_profile();
        $variables = [
            'percent' => $accountData->getStorageUsed(),
            'storageLimit' => ByteSizeConverter::bytesToShortFormat($accountData->getStorageLimit()),
            'storageUsed' => ByteSizeConverter::bytesToShortFormat($accountData->getStorageUsed()),
            'currentPlan' => $accountData->getCurrentPlanName(),
        ];

        return $variables;
    }
}