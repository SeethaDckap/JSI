<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Order;


/**
 * Recent Order block override
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Recent extends \Magento\Sales\Block\Order\Recent
{

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $orderCollectionFactory,
            $customerSession,
            $orderConfig,
            $data
        );
        $this->_accessauthorization = $context->getAccessAuthorization();
    }


    /**
     * Get order reorder url
     *
     * @param   \Epicor\Comm\Model\Order $order
     * @return  string
     */
    public function getReorderUrl($order)
    {
        return $this->getUrl('epicor/sales_order/reorder', array('order_id' => $order->getId()));
    }

    public function toHtml()
    {
        if (!$this->_accessauthorization->isAllowed(
            'Epicor_Customer::my_account_orders_read'
        )) {
            return '';
        }
        return \Magento\Framework\View\Element\Template::toHtml();
    }

}
