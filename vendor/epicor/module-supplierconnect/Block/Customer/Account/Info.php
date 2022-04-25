<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Account;


use Magento\Framework\Exception\NoSuchEntityException;



class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * Cached subscription object
     *
     * @var \Magento\Newsletter\Model\Subscriber
     */
    protected $_subscription;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_helperView;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    protected $registry;

    /**
     *  @var \Magento\Framework\DataObject
     */
    protected $_addressData;
    protected $_countryCode;

    /**
     * @var \Epicor\Supplierconnect\Helper\Data
     */
    protected $supplierconnectHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Helper\View $helperView
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Helper\View $helperView,
        array $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_helperView = $helperView;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->supplierconnectHelper = $supplierconnectHelper;
        $this->registry = $registry;
        $this->_addressData = $this->dataObjectFactory->create();
        if ($details = $this->registry->registry('supplier_connect_account_details')) {
            $this->_addressData = $details->getSupplierAddress();
        }
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct($context, $data);
    }

    /**
     * Returns the Magento Customer Model for this block
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        try {
            return $this->currentCustomer->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Get the full name of a customer
     *
     * @return string full name
     */
    public function getName()
    {
        return $this->_helperView->getCustomerName($this->getCustomer());
    }

    /**
     * @return string
     */
    public function getChangePasswordUrl()
    {
        return $this->_urlBuilder->getUrl('supplierconnect/account/edit/changepass/1');
    }

    /**
     * Get Customer Subscription Object Information
     *
     * @return \Magento\Newsletter\Model\Subscriber
     */
    public function getSubscriptionObject()
    {
        if (!$this->_subscription) {
            $this->_subscription = $this->_createSubscriber();
            $customer = $this->getCustomer();
            if ($customer) {
                $this->_subscription->loadByCustomerId($customer->getId());
            }
        }
        return $this->_subscription;
    }

    /**
     * Gets Customer subscription status
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsSubscribed()
    {
        return $this->getSubscriptionObject()->isSubscribed();
    }

    /**
     * Newsletter module availability
     *
     * @return bool
     */
    public function isNewsletterEnabled()
    {
        return $this->getLayout()
            ->getBlockSingleton(\Magento\Customer\Block\Form\Register::class)
            ->isNewsletterEnabled();
    }

    /**
     * @return \Magento\Newsletter\Model\Subscriber
     */
    protected function _createSubscriber()
    {
        return $this->_subscriberFactory->create();
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_accessauthorization->isAllowed(
            'Epicor_Supplier::supplier_dashboard_account_information'
        )) {
            return '';
        }

        return $this->currentCustomer->getCustomerId() ? parent::_toHtml() : '';
    }

    public function getAddressName()
    {
        return $this->_addressData->getName();
    }

    public function getStreet()
    {
        $street = $this->_addressData->getData('address1');
        $street .= $this->_addressData->getData('address2') ? ', ' . $this->_addressData->getData('address2') : '';
        $street .= $this->_addressData->getData('address3') ? ', ' . $this->_addressData->getData('address3') : '';
        return $street;
    }

    public function getCity()
    {
        return $this->_addressData->getCity();
    }

    public function getCounty()
    {
        $helper = $this->supplierconnectHelper;
        $region = $helper->getRegionFromCountyName($this->getCountryCode(), $this->_addressData->getCounty());

        return ($region) ? $region->getName() : $this->_addressData->getCounty();
    }

    public function getPostcode()
    {
        return $this->_addressData->getPostcode();
    }

    public function getCountryCode()
    {

        if (is_null($this->_countryCode)) {
            $helper = $this->supplierconnectHelper;
            $this->_countryCode = $helper->getCountryCodeForDisplay($this->_addressData->getCountry(), $helper::ERP_TO_MAGENTO);
        }

        return $this->_countryCode;
    }

    public function getCountry()
    {
        try {
            $helper = $this->supplierconnectHelper;
            return $helper->getCountryName($this->getCountryCode());
        } catch (\Exception $e) {
            return $this->_addressData->getCountry();
        }
    }

    public function getTelephoneNumber()
    {
        return $this->_addressData->getTelephoneNumber();
    }

    public function getFaxNumber()
    {
        return $this->_addressData->getFaxNumber();
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getAddressData()
    {
        return $this->_addressData;
    }

}
