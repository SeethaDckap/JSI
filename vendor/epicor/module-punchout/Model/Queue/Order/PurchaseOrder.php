<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Model
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Model\Queue\Order;

use Epicor\Punchout\Api\Data\Order\PurchaseOrderInterface;

/**
 * Class PurchaseOrder.
 */
class PurchaseOrder implements PurchaseOrderInterface
{

    /**
     * @var mixed
     */
    private $connectionId;

    /**
     * @var mixed
     */
    private $methodCode;

    /**
     * @var mixed
     */
    private $itemArray;

    /**
     * @var mixed
     */
    private $shopperId;

    /**
     * @var mixed
     */
    private $shippingAddressCode;

    /**
     * @var mixed
     */
    private $totals;

    /**
     * @var mixed
     */
    private $orderId;


    /**
     * Get connection ID.
     *
     * @return integer
     */
    public function getConnectionId()
    {
       return $this->connectionId;
    }

    /**
     * Set connection ID.
     *
     * @param integer $id Connection ID.
     *
     * @return mixed
     */
    public function setConnectionId($id)
    {
        $this->connectionId = $id;
    }

    /**
     * Get item array.
     *
     * @return mixed
     */
    public function getItemArray()
    {
        return $this->itemArray;
    }

    /**
     * Set item array.
     *
     * @param array $items Items array.
     *
     * @return mixed
     */
    public function setItemArray($items)
    {
        $this->itemArray = $items;
    }

    /**
     * Get customer ID.
     *
     * @return string
     */
    public function getCustomerId()
    {
        return $this->shopperId;
    }

    /**
     * Set customer email.
     *
     * @param integer $id ID.
     *
     * @return mixed
     */
    public function setCustomerId($id)
    {
        $this->shopperId = $id;
    }

    /**
     * Get address code.
     *
     * @return string
     */
    public function getShippingAddressCode()
    {
        return $this->shippingAddressCode;
    }

    /**
     * Set address code.
     *
     * @param string $addressCode Address code.
     *
     * @return mixed
     */
    public function setShippingAddressCode($addressCode)
    {
        $this->shippingAddressCode = $addressCode;
    }

    /**
     * Get shipping method code.
     *
     * @return mixed
     */
    public function getMethodCode()
    {
        return $this->methodCode;
    }

    /**
     * Set shipping method code.
     *
     * @param array $methodCode Shipping code.
     *
     * @return mixed
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
    }

    /**
     * Get totals array.
     *
     * @return mixed
     */
    public function getTotals()
    {
        return $this->totals;
    }

    /**
     * Set totals array.
     *
     * @param array $totals Totals.
     *
     * @return mixed
     */
    public function setTotals($totals)
    {
        $this->totals = $totals;

    }

    /**
     * Get order ID.
     *
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set order ID.
     *
     * @param mixed $orderId Order ID.
     *
     * @return mixed
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }


}//end class

