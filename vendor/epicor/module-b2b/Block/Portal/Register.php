<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Block\Portal;

/**
 * Customer register form block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Register extends \Magento\Customer\Block\Form\Register
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
        \Magento\Customer\Model\Url $customerUrl,
        array $data = []
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerSession = $customerSession;
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

        $acctOption = $this->scopeConfig->getValue('epicor_b2b/registration/b2b_acct_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$this->_request->getParam('prereg') &&  ($acctOption === 'erp_acct' || $acctOption === 'erp_acct_email')) {
            $this->setSendAccountToErp(true);
        }
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock( 'page.main.title' )->setPageTitle( __("CREATE A BUSINESS ACCOUNT"));
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
        $preReg = $this->_request->getParam('prereg');
        if ($preReg && $this->scopeConfig->isSetFlag('epicor_b2b/registration/pre_reg_pswd'))
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
                $formDataArray = is_array($formData) ? $formData : $formData->toArray();
                $data->addData($formDataArray);
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
        $result = $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_show_password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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

    public function getAllowedTypes()
    {
        return explode(',', $this->scopeConfig->getValue('epicor_b2b/registration/allowed_cus_types', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

    }

}
