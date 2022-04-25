<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Api\Data;

/**
 * CAAP DATA async Consumer Queue basic interface.
 */
interface CaapInfoInterface
{


    /**
     * Returns CAAP Order ID
     *
     * @return mixed
     */
    public function getOrderId();


    /**
     * Set CAAP Order Id
     *
     * @param string $orderId OrderId.
     *
     * @return mixed
     */
    public function setOrderId($orderId);
}
