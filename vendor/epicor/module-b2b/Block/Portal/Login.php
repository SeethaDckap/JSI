<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\B2b\Block\Portal;

/**
 * Customer login form block
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Login extends \Magento\Customer\Block\Form\Login
{

    private $_username = -1;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * Core url
     *
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $coreUrl;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Framework\Url\Helper\Data $coreUrl,
        array $data = []
    ) {
        $this->checkoutData = $checkoutData;
        $this->coreUrl = $coreUrl;
        $this->scopeConfig = $context->getScopeConfig();
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $customerSession,
            $customerUrl,
            $data
        );
    }


    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Customer Login'));
        return parent::_prepareLayout();
    }

    //M1 > M2 Translation Begin (Rule 59)
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSession(){
        return $this->customerSession;
    }
    //M1 > M2 Translation End

    public function showCustomerRegistration()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_customer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function showBusinessRegistration()
    {
        $regPortal = $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_portal', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $preReg = $this->scopeConfig->isSetFlag('epicor_b2b/registration/pre_reg_pswd', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $option = $this->scopeConfig->getValue('epicor_b2b/registration/b2b_acct_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return ($regPortal && ($preReg || $option !== 'disable_new_erp_acct'));
    }

    public function showCreateAcct()
    {
        $option = $this->scopeConfig->getValue('epicor_b2b/registration/b2b_acct_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_portal', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $option !== 'disable_new_erp_acct';
    }

    public function showPreRegistrationPswd()
    {
        return $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_portal', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->scopeConfig->isSetFlag('epicor_b2b/registration/pre_reg_pswd', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        //M1 > M2 Translation Begin (Rule 58)
        //return $this->helper('customer')->getLoginPostUrl();
        return $this->_customerUrl->getLoginPostUrl();
        //M1 > M2 Translation End
    }

    /**
     * Retrieve create new account url
     *
     * @return string
     */
    public function getB2bCreateAccountUrl($isPreRegPswd)
    {
        return $this->getUrl('b2b/portal/register', array('prereg' => $isPreRegPswd));
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

    public function getCreateAccountUrl()
    {
        $url = $this->getData('create_account_url');
        if ($url === null) {
            $url = $this->_customerUrl->getRegisterUrl();
        }
        if ($this->checkoutData->isContextCheckout()) {
            $url = $this->coreUrl->addRequestParam($url, ['context' => 'checkout']);
        }
        return $url;
    }
    public function getHomeRegistrationTitle()
    {
        $homeRegistration = $this->getConfig('epicor_b2b/registration/home_reg_heading_wording');
        //if it has been blanked, ensure something is returned
        return $homeRegistration ? $homeRegistration : 'Home Customer';
    }
    public function getHomeRegistrationContent()
    {
        return $this->getConfig('epicor_b2b/registration/home_reg_content_wording');
    }
    public function getHomeRegistrationButtonWording()
    {
        $homeRegistrationButton = $this->getConfig('epicor_b2b/registration/home_reg_button_wording');
        //if it has been blanked, ensure something is returned
        return $homeRegistrationButton ? $homeRegistrationButton : 'Create an Account';
    }
    public function getBusinessRegistrationTitle()
    {
        $busRegistrationTitle = $this->getConfig('epicor_b2b/registration/bus_reg_heading_wording');
        //if it has been blanked, ensure something is returned
        return $busRegistrationTitle ? $busRegistrationTitle : 'Business Customers';
    }
    public function getBusinessRegistrationContent()
    {
        $busRegistrationContent = $this->getConfig('epicor_b2b/registration/bus_reg_content_wording');
        return $busRegistrationContent;
    }
    public function getBusinessRegistrationButtonWording()
    {
        $busRegistrationButton = $this->getConfig('epicor_b2b/registration/bus_reg_button_wording');
        //if it has been blanked, ensure something is returned
        return $busRegistrationButton ? $busRegistrationButton : 'Create a Business Account';
    }
}
