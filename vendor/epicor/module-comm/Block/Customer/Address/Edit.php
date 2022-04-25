<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Address;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

use Epicor\Common\Helper\Data;

/**
 * Description of Edit
 *
 * @author David.Wylie
 */
class Edit extends \Magento\Customer\Block\Address\Edit
{
    const XML_PATH_DEFAULT_SHIPPING_OVERRIDE = 'epicor_comm_field_mapping/cus_mapping/cus_default_shipping_override';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customer;

    /**
     * Common helper.
     *
     * @var Data
     */
    private $commonHelper;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        Data $commonHelper,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->addressFactory = $addressFactory;
        $this->customer = $customer;
        $this->commonHelper = $commonHelper;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $customerSession,
            $addressRepository,
            $addressDataFactory,
            $currentCustomer,
            $dataObjectHelper,
            $data
        );
    }


    public function getMaxCommentSize()
    {
        if ($this->limitTextArea()) {
            return $this->scopeConfig->getValue('checkout/options/max_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return '';
    }

    public function limitTextArea()
    {
        $result = false;
        if ($this->scopeConfig->isSetFlag('checkout/options/limit_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $value = $this->scopeConfig->getValue('checkout/options/max_comment_length', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (is_numeric($value)) {
                $result = true;
            }
        }
        return $result;
    }

    public function getRemainingCommentSize()
    {
        $max = $this->getMaxCommentSize();
        $current = $this->getAddressModel()->getEccInstructions();
        return $max - strlen($current);
    }

    public function canMarkDefaultShippingBillingAddress()
    {
        if ($this->getCustomer()->isGuest()) {
            return true;
        }

        return false;
    }
    public function getAddressModel()
    {
        $addressmodel =  $this->addressFactory->create();
        if ($addressId = $this->getRequest()->getParam('id')) {
            $addressmodel =  $this->addressFactory->create()->load($addressId);
        }
        return $addressmodel;
        
    }
        /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        //change from $this->currentCustomer->getCustomer()->getId(); as when guest error occurs
        $customerId = $this->currentCustomer->getCustomerId();
        return $this->customer->create()->load($customerId);;
    }

    /**
     * Generate name block html.
     *
     * @return string
     */
    public function getNameBlockHtml()
    {
        if($this->getAddress()->getId()){
            $object = $this->getAddress();
        }else{
            $object = $this->getCustomer();
        }
        $nameBlock = $this->getLayout()
            ->createBlock('Magento\Customer\Block\Widget\Name')
            ->setObject($object);

        return $nameBlock->toHtml();
    }


    /**
     * Allow override default shipping address.
     *
     * @return boolean
     */
    public function canOverrideDefaultShipping()
    {
        $allowOverride = $this->_scopeConfig->isSetFlag(
            self::XML_PATH_DEFAULT_SHIPPING_OVERRIDE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $isGuest = $this->getCustomer()->isGuest();

        return ($allowOverride && !$isGuest);

    }//end canOverrideDefaultShipping()


    /**
     * Allow override default shipping address.
     *
     * @return boolean
     */
    public function getSaveDefaultAddressUrl()
    {
        return $this->_urlBuilder->getUrl(
            'comm/customerAddress/SaveDefaults',
            [
                '_secure' => true,
                'id'      => $this->getAddress()->getId(),
            ]
        );

    }//end getSaveDefaultAddressUrl()


    /**
     * Show address HTML.
     *
     * @return boolean
     */
    public function showAddressHtml()
    {
        $erpAddressCode = $this->getAddressModel()->getEccErpAddressCode();
        $hasErpAddressCode = (
            empty($erpAddressCode)
            && $erpAddressCode !== 0
            && $erpAddressCode !== '0'
        ) ? null : true;

        $allowAddition  = $this->commonHelper->customerAddressPermissionCheck('create');
        $isGuest        = $this->getCustomer()->isGuest();

        return !$isGuest && ($hasErpAddressCode || ($hasErpAddressCode == null && !$allowAddition));

    }//end showAddressHtml()


    /**
     * Can edit.
     *
     * @return boolean
     */
    public function canEdit()
    {
        $erpAddressCode = $this->getAddressModel()->getEccErpAddressCode();
        $hasErpAddressCode = (
            empty($erpAddressCode)
            && $erpAddressCode !== 0
            && $erpAddressCode !== '0'
        ) ? null : true;
        $isGuest        = $this->getCustomer()->isGuest();

        return ($hasErpAddressCode == null) || $isGuest;

    }//end canEdit()


}


