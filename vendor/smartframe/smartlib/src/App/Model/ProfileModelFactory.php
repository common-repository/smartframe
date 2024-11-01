<?php

namespace SmartFrameLib\App\Model;
if (!defined('ABSPATH')) exit;

/**
 * Class ProfileModel
 * @package SmartFrameLib\App\Model
 */
class ProfileModelFactory
{

    /**
     * @param $json
     * @return ProfileModel
     */
    public static function createFromStringJson($json)
    {
        $isValid = false;
        ini_set('serialize_precision', 25);
        ini_set('precision', 25);
        $data = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);
        if ($data === null) {
            $data['currentPlan']['storageLimit'] = 0;
            $data['storageUsed'] = 0;
            $data['storageUsedInBytes'] = 0;
            $data['currentPlan']['name'] = '';
            $data['name'] = '';
            $data['newImageMetadataMode'] = 'V1';
            $data['publicId'] = '';
            $data['smartframejs'] = '';
            $data['isActive'] = false;
            $data['email'] = '';
            $data['wpPluginApiKey'] = '';
            $data['externalImageSource'] = '';
            $data['payment']['lastPayment'] = false;
            $data['payment']['nextPayment'] = false;
            $data['payment']['paymentStatus'] = Payment::STATUS_UNKNOWN;
        }

        $isValid = isset($data['payment']);

        if (!$isValid) {
            $data['payment']['lastPayment'] = false;
            $data['payment']['nextPayment'] = false;
            $data['payment']['paymentStatus'] = Payment::STATUS_UNKNOWN;
        }
        return new ProfileModel($data);
    }

}