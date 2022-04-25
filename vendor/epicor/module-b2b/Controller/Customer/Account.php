<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Controller\Customer;


//include_once("Mage/Customer/controllers/AccountController.php");

abstract class Account extends \Magento\Customer\Controller\AbstractAccount
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Cache\StateInterface $state
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        $this->_cacheState = $state;
        parent::__construct(
            $context
        );
    }


    public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true)
    {
        if ((!$this->_getSession()->isLoggedIn()) && $this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_portaltype', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $customHandle = 'use_portal';
            parent::loadLayout($handles, false);
            $update = $this->getLayout()->getUpdate();
            $update->addHandle($customHandle);
            //M1 > M2 Translation Begin (Rule 12)
            /*if (Mage::app()->useCache('layout')) {
                $cacheId = $update->getCacheId() . $customHandle;
                $update->setCacheId($cacheId);

                if (!$this->cache->load($cacheId)) {
                    foreach ($update->getHandles() as $handle) {
                        $update->merge($handle);
                    }
                    $update->saveCache();
                } else {
                    //load updates from cache
                    $update->load();
                }
            } else {
                //load updates
                $update->load();
            }*/
            if ($this->_cacheState->isEnabled('layout')) {
                $cacheId = $update->getCacheId() . $customHandle;
                $update->setCacheId($cacheId);

                if (!$this->cache->load($cacheId)) {
                    foreach ($update->getHandles() as $handle) {
                        $update->load($handle);
                    }
                    $update->saveCache();
                } else {
                    $update->load();
                }
            } else {
                $update->load();
            }
            //M1 > M2 Translation End
            $this->loadLayoutUpdates();
            if ($generateXml)
                $this->generateLayoutXml();
            if ($generateBlocks)
                $this->generateLayoutBlocks();
        } else
            parent::loadLayout($handles, $generateBlocks, $generateXml);
    }

    protected function _redirectSuccess($defaultUrl)
    {
        parent::_redirectSuccess($defaultUrl);
    }

    /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(\Magento\Customer\Model\Customer $customer, $isJustConfirmed = false)
    {
        $customeWelcomeMessage = $this->__($this->scopeConfig->getValue('epicor_b2b/registration/customer_success_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        if (empty($customeWelcomeMessage)) {
            return parent::_welcomeCustomer($customer, $isJustConfirmed);
        }

        $this->_getSession()->addSuccess($customeWelcomeMessage);
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType = $this->_getHelper('customer/address')->getTaxCalculationAddressType();
            $userPrompt = '';
            switch ($configAddressType) {
                case Mage_Customer_Model_Address_Generic::TYPE_SHIPPING:
                    //M1 > M2 Translation Begin (Rule 55)
                    //$userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you shipping address for proper VAT calculation', $this->_getUrl('customer/address/edit'));
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%1">here</a> to enter you shipping address for proper VAT calculation', $this->_getUrl('customer/address/edit'));
                    //M1 > M2 Translation End
                    break;
                default:
                    //M1 > M2 Translation Begin (Rule 55)
                    //$userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%s">here</a> to enter you billing address for proper VAT calculation', $this->_getUrl('customer/address/edit'));
                    $userPrompt = $this->__('If you are a registered VAT customer, please click <a href="%1">here</a> to enter you billing address for proper VAT calculation', $this->_getUrl('customer/address/edit'));
                //M1 > M2 Translation End
            }
            $this->_getSession()->addSuccess($userPrompt);
        }

        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered', '', $this->storeManager->getStore()->getId()
        );

        $successUrl = $this->_getUrl('*/*/index', array('_secure' => true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

}
