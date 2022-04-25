<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\B2b\Block\Portal\Form;

use Magento\Customer\Model\AccountManagement;

/**
 * Customer register form block
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Register extends \Magento\Customer\Block\Form\Register
{
    /**
     * Scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /*
     * @var \Magento\Framework\App\ProductMetadataInterface 
     */
    protected $_productMetadata;
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_productMetadata = $productMetadata;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $moduleManager,
            $customerSession,
            $customerUrl,
            $data
        );
        $this->setShowAddressFields($this->scopeConfig->isSetFlag('epicor_b2b/registration/b2c_show_address_fields', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $b2bAcctType = $this->scopeConfig->getValue('epicor_b2b/registration/b2c_acct_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($b2bAcctType == 'erp_acct' || $b2bAcctType == 'erp_acct_email') {
            $this->setSendAccountToErp(true);
        }
    }

    public function showDeliveryAddress()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/b2c_delivery_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showInvoiceAddress()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/b2c_invoice_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showRegisteredAddress()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/b2c_registered_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showDeliveryAddressTelephoneFax()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/b2c_delivery_address_phone_fax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showInvoiceAddressTelephoneFax()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/b2c_invoice_address_phone_fax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showRegisteredAddressTelephoneFax()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/b2c_registered_address_phone_fax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function displayMobilePhone()
    {
        return $this->scopeConfig->isSetFlag('customer/address/display_mobile_phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function displayInstructions()
    {
        return $this->scopeConfig->isSetFlag('customer/address/display_instructions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getConfigFlag($path)
    {
        return $this->_scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMagentoVersion()
    {
       return $this->_productMetadata->getVersion();
    }

    public function escapeHtmlAttr($string,$escapeSingleQuote = true)
    {
       if($this->getMagentoVersion()< '2.2.0'){
           return $string;
       }else{
           return parent::escapeHtmlAttr($string, $escapeSingleQuote);
       }
    }
}
