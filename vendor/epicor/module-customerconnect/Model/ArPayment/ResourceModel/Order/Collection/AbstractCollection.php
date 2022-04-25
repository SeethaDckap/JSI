<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Collection;

/**
 * Flat sales order collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractCollection extends \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Collection\AbstractCollection
{
    /**
     * Order object
     *
     * @var \Epicor\Customerconnect\Model\ArPayment\Order
     */
    protected $_salesOrder = null;

    /**
     * Order field for setOrderFilter
     *
     * @var string
     */
    protected $_orderField = 'parent_id';

    /**
     * Set sales order model as parent collection object
     *
     * @param \Epicor\Customerconnect\Model\ArPayment\Order $order
     * @return $this
     */
    public function setSalesOrder($order)
    {
        $this->_salesOrder = $order;
        if ($this->_eventPrefix && $this->_eventObject) {
            $this->_eventManager->dispatch(
                'ar_'.$this->_eventPrefix . '_set_sales_order',
                ['collection' => $this, $this->_eventObject => $this, 'order' => $order]
            );
        }

        return $this;
    }

    /**
     * Retrieve sales order as parent collection object
     *
     * @return \Epicor\Customerconnect\Model\ArPayment\Order|null
     */
    public function getSalesOrder()
    {
        return $this->_salesOrder;
    }

    /**
     * Add order filter
     *
     * @param int|\Epicor\Customerconnect\Model\ArPayment\Order|array $order
     * @return $this
     */
    public function setOrderFilter($order)
    {
        if ($order instanceof \Epicor\Customerconnect\Model\ArPayment\Order) {
            $this->setSalesOrder($order);
            $orderId = $order->getId();
            if ($orderId) {
                $this->addFieldToFilter($this->_orderField, $orderId);
            } else {
                $this->_totalRecords = 0;
                $this->_setIsLoaded(true);
            }
        } else {
            $this->addFieldToFilter($this->_orderField, $order);
        }
        return $this;
    }
}
