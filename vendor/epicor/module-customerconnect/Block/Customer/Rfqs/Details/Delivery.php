<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * RFQ delivery address block
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Delivery extends \Epicor\Customerconnect\Block\Customer\Address
{
    const FRONTEND_RESOURCE_BILLING_READ = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_BILLING_UPDATE = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

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
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commHelper = $commHelper;
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
        $order = $this->registry->registry('customer_connect_rfq_details');
        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/rfqs/details/address.phtml');

        if ($this->registry->registry('rfq_new')) {
            $customer = $this->customerSession->getCustomer();

            $helper = $this->commHelper;

            if ($helper->isMasquerading()) {
                $this->registry->register('masq_address_hide_customer_name', true, true);
            }

            $this->setAddressFromCustomerAddress($customer->getPrimaryShippingAddress());
            $this->setShowName(false);
        } else if ($order) {
            $this->_addressData = $order->getDeliveryAddress();
            $this->setShowName(true);
        }

        $this->setTitle(__('Ship To'));
        $this->setOnRight(true);
        $this->setAddressType('delivery');
        $this->setShowUpdateLink(true);
    }

    public function isEditable()
    {
        return $this->registry->registry('rfqs_editable');
    }

}
