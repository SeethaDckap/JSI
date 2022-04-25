<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Orders\Details;


class Shipping extends \Epicor\Customerconnect\Block\Customer\Address
{
    const FRONTEND_RESOURCE_BILLING_READ = 'Epicor_Customerconnect::customerconnect_account_orders_details';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $customerconnectHelper,
            $commMessagingHelper,
            $customerSession,
            $commonHelper,
            $dataObjectFactory,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $order = $this->registry->registry('customer_connect_order_details');
        if ($order) {
            $this->_addressData = $order->getDeliveryAddress();
            $this->setTitle(__('Ship To'));
            $this->setOnRight(true);
        }
    }

}
