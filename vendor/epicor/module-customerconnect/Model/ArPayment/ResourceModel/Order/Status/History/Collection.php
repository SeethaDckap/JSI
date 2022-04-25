<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Status\History;

use Epicor\Customerconnect\Model\ArPayment\AbstractModel;
use Epicor\Customerconnect\Model\ArPayment\Order;
use Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Collection\AbstractCollection;

/**
 * Flat sales order status history collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'ecc_ar_sales_order_status_history_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'order_status_history_collection';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Epicor\Customerconnect\Model\ArPayment\Order\Status\History::class,
            \Epicor\Customerconnect\Model\ArPayment\ResourceModel\Order\Status\History::class
        );
    }

    /**
     * Get history object collection for specified instance (order, shipment, invoice or credit memo)
     * Parameter instance may be one of the following types: \Epicor\Customerconnect\Model\ArPayment\Order,
     * \Epicor\Customerconnect\Model\ArPayment\Order\Creditmemo, \Epicor\Customerconnect\Model\ArPayment\Order\Invoice, \Epicor\Customerconnect\Model\ArPayment\Order\Shipment
     *
     * @param AbstractModel $instance
     * @return \Epicor\Customerconnect\Model\ArPayment\Order\Status\History|null
     */
    public function getUnnotifiedForInstance($instance)
    {
        if (!$instance instanceof Order) {
            $instance = $instance->getOrder();
        }
        $this->setOrderFilter(
            $instance
        )->setOrder(
            'created_at',
            'desc'
        )->addFieldToFilter(
            'entity_name',
            $instance->getEntityType()
        )->addFieldToFilter(
            'is_customer_notified',
            0
        )->setPageSize(
            1
        );
        foreach ($this->getItems() as $historyItem) {
            return $historyItem;
        }
        return null;
    }
}
