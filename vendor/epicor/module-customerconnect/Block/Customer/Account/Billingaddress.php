<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Account;


class Billingaddress extends \Epicor\Customerconnect\Block\Customer\Address
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

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
        \Epicor\Common\Helper\Access $commonAccessHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
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
        $details = $this->registry->registry('customer_connect_account_details');

        if ($details) {
            $this->_addressData = $details->getInvoiceAddress();

            $helper = $this->commonAccessHelper;

            $this->setShowUpdateLink($helper->customerHasAccess('Epicor_Customerconnect', 'Account', 'saveBillingAddress', '', 'Access'));

            if ($this->getShowUpdateLink() && !$this->editAllowed()) {
                $this->setShowUpdateLink(false);
            }
            $this->setFormSaveUrl($this->getUrl('customerconnect/account/saveBillingAddress'));
        }
        $this->setTitle(__('Billing'));
        $this->setAddressType('billing');
    }

}
