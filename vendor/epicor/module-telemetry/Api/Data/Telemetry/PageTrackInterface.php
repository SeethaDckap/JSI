<?php
/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Telemetry\Api\Data\Telemetry;

/**
 * Page Track data async Consumer Queue basic interface.
 */
interface PageTrackInterface
{

    /**
     * @return mixed
     */
    public function getEventName();

    /**
     * @param $name
     * @return mixed
     */
    public function setEventName($name);

    /**
     * @return mixed
     */
    public function getShippingCode();

    /**
     * @param mixed $shippingCode
     * @return mixed
     */
    public function setShippingCode($shippingCode);

    /**
     * @return mixed
     */
    public function getShippingTitle();

    /**
     * @param mixed $shippingTitle
     * @return mixed
     */
    public function setShippingTitle($shippingTitle);

    /**
     * @return mixed
     */
    public function getPaymentCode();

    /**
     * @param mixed $paymentCode
     * @return mixed
     */
    public function setPaymentCode($paymentCode);

    /**
     * @return mixed
     */
    public function getPaymentTitle();

    /**
     * @param mixed $paymentTitle
     * @return mixed
     */
    public function setPaymentTitle($paymentTitle);

}
