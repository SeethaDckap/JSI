<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Address;

/**
 * Customer register form block for AR Payments
 *
 * 
 */
class Address extends \Magento\Customer\Block\Form\Register
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    
    
    protected $arPaymentsModel;
    
    /**
     * @var \Epicor\Customerconnect\Model\ArPayment\Quote\Address
     */
    protected $checkoutSession;    

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Customerconnect\Model\Arpayments $arPaymentsModel,
        \Epicor\Customerconnect\Model\ArPayment\Session $checkoutSession,
        \Magento\Customer\Model\Url $customerUrl,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerSession = $customerSession;
        $this->arPaymentsModel = $arPaymentsModel;
        $this->checkoutSession = $checkoutSession;
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
        $this->setShowAddressFields($this->scopeConfig->isSetFlag('epicor_b2b/registration/show_address_fields', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        if ($this->scopeConfig->getValue('epicor_b2b/registration/reg_options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'cnc') {
            $this->setSendAccountToErp(true);
        }
    }

    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__(''));

        //$this->getLayout()->getBlock('head')->setTitle(__('Create New Customer Account'));
        return parent::_prepareLayout();
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('b2b/portal/registerpost');
    }

    public function getBackUrl()
    {
        return $this->getUrl('b2b/portal/login');
    }

    public function showPreReg()
    {
        if ($this->scopeConfig->getValue('epicor_b2b/registration/reg_options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'prereg' ||
            $this->scopeConfig->isSetFlag('epicor_b2b/registration/prereg_active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        )
            return true;
        else
            return false;
    }

    /**
     * Retrieve form data
     *
     * @return \Magento\Framework\DataObject
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (is_null($data)) {
            $formData = $this->customerSession->getCustomerFormData(true);
            $data = $this->dataObjectFactory->create();
            if ($formData) {
                $data->addData($formData);
                $data->setCustomerData(1);
            }
            if (isset($data['region_id'])) {
                $data['region_id'] = (int) $data['region_id'];
            }

            if (!isset($data['delivery'])) {
                $data['delivery'] = array();
            }

            if (!isset($data['invoice'])) {
                $data['invoice'] = array();
            }

            if (!isset($data['registered'])) {
                $data['registered'] = array();
            }

            $this->setData('form_data', $data);
        }


        return $data;
    }

    public function showPasswordField()
    {
        $result = true;
        if ($this->scopeConfig->getValue('epicor_b2b/registration/reg_options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'email_request') {
            $result = $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_show_password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return $result;
    }

    public function renderAddressForm($type)
    {
        $block = $this->getLayout()->createBlock('\Epicor\Common\Block\Customer\Erpaccount\Address');
        //$block->initForm();
        return $block->getAddressHtml($type, array());
    }

    public function showDeliveryAddress()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/delivery_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showInvoiceAddress()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/invoice_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showRegisteredAddress()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/registered_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showDeliveryAddressTelephoneFax()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/delivery_address_phone_fax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showInvoiceAddressTelephoneFax()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/invoice_address_phone_fax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showRegisteredAddressTelephoneFax()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/registered_address_phone_fax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function displayMobilePhone()
    {
        return $this->scopeConfig->isSetFlag('customer/address/display_mobile_phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function displayInstructions()
    {
        return $this->scopeConfig->isSetFlag('customer/address/display_instructions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @param $path
     * @return bool
     */
    public function getConfigFlag($path)
    {
        return $this->_scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    //M1 > M2 Translation End
    
    
    public function getErpAddressList()
    {
        return $this->arPaymentsModel->getErpAddressList();
    }
    
    public function getArAddress() {
       $quoteBilling = $this->checkoutSession->getQuote()->getBillingAddress();   
       if($quoteBilling->getCustomerNotes() =="newaddress") {
           return $quoteBilling->getData();
       } else {
           return false;
       }
    }

    public function getArAddressStreet() {
       $quoteBilling = $this->checkoutSession->getQuote()->getBillingAddress();   
       if($quoteBilling->getCustomerNotes() =="newaddress") {
           return $quoteBilling->getStreet();
       } else {
           return false;
       }
    }

    private function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path);
    }

    public function getValidationLimit($type)
    {
        switch ($type) {
            case 'name':
                $path = 'customer/address/limit_name_length';
                break;
            case 'line':
                $path = 'customer/address/limit_address_line_length';
                break;
            case 'telephone':
                $path = 'customer/address/limit_telephone_length';
                break;
            case 'instructions':
                $path = 'customer/address/limit_instructions_length';
                break;
            case 'postcode':
                $path = 'customer/address/limit_postcode_length';
                break;
            case 'lastname':
                $path = 'customer/address/limit_lastname_length';
                break;
            case 'company':
                $path = 'customer/address/limit_company_length';
                break;
            case 'email':
                $path = 'customer/address/limit_email_length';
                break;
            default:
                $path = false;
        }

        if($path && $config = $this->getConfigValue($path)){
            return $config;
        }
        return 10234;
    }
    
}