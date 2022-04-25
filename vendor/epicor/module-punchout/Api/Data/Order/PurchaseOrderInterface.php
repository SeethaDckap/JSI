<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Api
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Punchout\Api\Data\Order;

/**
 * Purchase order async Consumer Queue basic interface.
 */
interface PurchaseOrderInterface
{


    /**
     * Get connection ID.
     *
     * @return integer
     */
    public function getConnectionId();


    /**
     * Set connection ID.
     *
     * @param integer $id Connection ID.
     *
     * @return mixed
     */
    public function setConnectionId($id);


    /**
     * Get item array.
     *
     * @return mixed
     */
    public function getItemArray();


    /**
     * Set item array.
     *
     * @param array $items Items array.
     *
     * @return mixed
     */
    public function setItemArray($items);


    /**
     * Get customer ID.
     *
     * @return integer
     */
    public function getCustomerId();


    /**
     * Set customer ID.
     *
     * @param integer $id ID.
     *
     * @return mixed
     */
    public function setCustomerId($id);


    /**
     * Get address code.
     *
     * @return string
     */
    public function getShippingAddressCode();


    /**
     * Set address code.
     *
     * @param string $addressCode Address code.
     *
     * @return mixed
     */
    public function setShippingAddressCode($addressCode);


    /**
     * Get shipping method code.
     *
     * @return mixed
     */
    public function getMethodCode();


    /**
     * Set shipping method code.
     *
     * @param array $methodCode Shipping code.
     *
     * @return mixed
     */
    public function setMethodCode($methodCode);


    /**
     * Get totals array.
     *
     * @return mixed
     */
    public function getTotals();


    /**
     * Set totals array.
     *
     * @param array $totals Totals.
     *
     * @return mixed
     */
    public function setTotals($totals);


    /**
     * Get order ID.
     *
     * @return mixed
     */
    public function getOrderId();


    /**
     * Set order ID.
     *
     * @param mixed $orderId Order ID.
     *
     * @return mixed
     */
    public function setOrderId($orderId);


}//end class

