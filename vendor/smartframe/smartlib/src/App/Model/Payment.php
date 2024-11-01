<?php

namespace SmartFrameLib\App\Model;
 if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class ProfileModel
 * @package SmartFrameLib\App\Model
 */
class Payment
{
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_CAPTURED = 'captured';
    const STATUS_PARTIALLY_CAPTURED = 'partially_captured';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';
    const STATUS_UNCAPTURED = 'uncaptured';
    const STATUS_UNKNOWN = 'unknown';
     
    private $paymentStatus;

    private $latPaymentDate;

    private $nextPaymentDate;

    /**
     * Payment constructor.
     */
    public function __construct($arrayData)
    {
        $this->latPaymentDate = $arrayData['lastPayment'];
        $this->nextPaymentDate = $arrayData['nextPayment'];
        $this->paymentStatus = $arrayData['paymentStatus'];
    }

    /**
     * @return mixed
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @return mixed
     */
    public function getLatPaymentDate()
    {
        return $this->latPaymentDate;
    }

    /**
     * @return mixed
     */
    public function getNextPaymentDate()
    {
        return $this->nextPaymentDate;
    }

}