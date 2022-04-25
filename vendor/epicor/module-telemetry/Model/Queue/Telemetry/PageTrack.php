<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Telemetry\Model\Queue\Telemetry;

use Epicor\Telemetry\Api\Data\Telemetry\PageTrackInterface;

/**
 * Class PageTrack.
 * @category   Epicor
 * @package    Epicor_Telemetry
 * @author     Epicor Websales Team
 */
class PageTrack implements PageTrackInterface
{
    /**
     * @var mixed
     */
    private $eventName;

    /**
     * @var mixed
     */
    private $shippingCode;

    /**
     * @var mixed
     */
    private $shippingTitle;

    /**
     * @var mixed
     */
    private $paymentCode;

    /**
     * @var mixed
     */
    private $paymentTitle;


    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @param mixed $name
     * @return mixed|void
     */
    public function setEventName($name)
    {
        $this->eventName = $name;
        return;
    }

    /**
     * @return mixed
     */
    public function getShippingCode()
    {
        return $this->shippingCode;
    }

    /**
     * @param mixed $shippingCode
     * @return mixed|void
     */
    public function setShippingCode($shippingCode)
    {
        $this->shippingCode = $shippingCode;
    }

    /**
     * @return mixed
     */
    public function getShippingTitle()
    {
        return $this->shippingTitle;
    }

    /**
     * @param mixed $shippingTitle
     * @return mixed|void
     */
    public function setShippingTitle($shippingTitle)
    {
        $this->shippingTitle = $shippingTitle;
    }

    /**
     * @return mixed
     */
    public function getPaymentCode()
    {
        return $this->paymentCode;
    }

    /**
     * @param mixed $paymentCode
     * @return mixed|void
     */
    public function setPaymentCode($paymentCode)
    {
        $this->paymentCode = $paymentCode;
    }

    /**
     * @return mixed
     */
    public function getPaymentTitle()
    {
        return $this->paymentTitle;
    }

    /**
     * @param mixed $paymentTitle
     * @return mixed|void
     */
    public function setPaymentTitle($paymentTitle)
    {
        $this->paymentTitle = $paymentTitle;
    }

}
