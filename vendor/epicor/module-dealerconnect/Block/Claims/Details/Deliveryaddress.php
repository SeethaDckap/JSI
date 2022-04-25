<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims\Details;


/**
 * RFQ delivery address block
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Deliveryaddress extends \Epicor\Customerconnect\Block\Customer\Address
{

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
        $order = $this->registry->registry('dealer_connect_claim_details');
        $this->setTemplate('Epicor_Dealerconnect::claims/details/address.phtml');

        if ($this->registry->registry('claim_new')) {
            $customer = $this->customerSession->getCustomer();

            $helper = $this->commHelper;

            if ($helper->isMasquerading()) {
                $this->registry->register('masq_address_hide_customer_name', true, true);
            }

            $this->setAddressFromCustomerAddress($customer->getPrimaryShippingAddress());
            $this->setShowName(false);
        } else {
            if($order) {
                $this->_addressData = $order->getDeliveryAddress();
            }
            $this->setShowName(true);
        }

        $this->setTitle(__('Delivery Address'));
        $this->setOnRight(true);
        $this->setAddressType('delivery');
        $this->setShowUpdateLink(true);
    }

    public function isEditable()
    {
        return $this->registry->registry('claims_editable');
    }

}
