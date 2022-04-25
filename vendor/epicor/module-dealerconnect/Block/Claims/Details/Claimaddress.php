<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims\Details;


/**
 *
 * Claim address block
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Claimaddress extends \Epicor\Customerconnect\Block\Customer\Address
{

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
        $order = $this->registry->registry('dealer_connect_claim_details');
        $this->setTemplate('Epicor_Dealerconnect::claims/details/address.phtml');

        if ($this->registry->registry('claim_new')) {
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
        } else {
            $this->setShowName(true);
            if($order){
                $this->_addressData = $order->getClaimAddress();
            }
        }
        $this->setTitle(__('Claim Address'));
        $this->setAddressType('claim');
        $this->setShowUpdateLink(true);
    }

    public function isEditable()
    {
        return $this->getAddressType() == 'claim' ? false : $this->registry->registry('claims_editable');
    }

}
