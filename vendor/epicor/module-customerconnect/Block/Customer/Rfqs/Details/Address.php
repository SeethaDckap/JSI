<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 *
 * RFQ address block
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Address extends \Epicor\Customerconnect\Block\Customer\Address
{
    const FRONTEND_RESOURCE_BILLING_READ = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_BILLING_UPDATE = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

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
        $order = $this->registry->registry('customer_connect_rfq_details');
        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/rfqs/details/address.phtml');

        if ($this->registry->registry('rfq_new')) {
            $customer = $this->customerSession->getCustomer();
            /* @var $customer \Epicor\Comm\Model\Customer */

            if (!$customer->getId()) {
                $customer = $customer->load($this->customerSession->getId());
                $this->customerSession->setCustomer($customer);
            }

            $addresses = $customer->getAddressesByType('registered');

            if (!empty($addresses)) {
                $address = array_pop($addresses);
            } else {
                $address = $customer->getDefaultBillingAddress();
            }

            $this->setAddressFromCustomerAddress($address);
            $this->setShowName(false);
        } else if($order) {
            $this->setShowName(true);
            $this->_addressData = $order->getQuoteAddress();
        }
        $this->setTitle(__('Sold To'));
        $this->setAddressType('quote');
        $this->setShowUpdateLink(true);
    }

    public function isEditable()
    {
        return $this->getAddressType() == 'quote' ? false : $this->registry->registry('rfqs_editable');
    }

}
