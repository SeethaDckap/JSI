<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Queue\Entity;

use Epicor\Comm\Api\Data\CaapInfoInterface;

/**
 * Class CaapInfo.
 */
class CaapInfo implements CaapInfoInterface
{

    /**
     * Caap Order Id.
     *
     * @var string
     */
    private $orderId;


    /**
     * Get Order Id.
     *
     * @return mixed|string
     */
    public function getOrderId()
    {
        return $this->orderId;

    }//end getOrderId()


    /**
     * Set Order Id.
     *
     * @param string $orderId OrderId.
     *
     * @return mixed|void
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

    }//end setOrderId()


}
