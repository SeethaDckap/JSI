<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Observer;

class ModifyBlockHtmlBefore extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerCustomer;
    
    /**
     * @var \Epicor\SalesRep\Helper\Data
     */
    protected $salesRepHelper;
protected $arPaymentsModel;
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper, \Magento\Customer\Model\Session $customerSession, \Epicor\Comm\Helper\Data $commHelper, \Epicor\Common\Helper\Access $commonAccessHelper, \Epicor\Comm\Model\Customer $customerCustomer, \Epicor\SalesRep\Helper\Data $salesRepHelper,\Epicor\Customerconnect\Model\Arpayments $arPaymentsModel
    ) {
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->customerSession = $customerSession;
        $this->commHelper = $commHelper;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerCustomer = $customerCustomer;
        $this->scopeConfig = $context->getScopeConfig();
        $this->salesRepHelper = $salesRepHelper;
        $this->arPaymentsModel=$arPaymentsModel;
    }

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        $block = $observer->getEvent()->getBlock();
        //echo get_class($block);

        if ($block instanceof \Epicor\Lists\Block\Contract\Link) {
            $helper = $this->listsFrontendContractHelper;
            if (
                    $helper->contractsDisabled() ||
                    count($helper->getActiveContracts()) == 0
            ) {
                $block->contractAllowed = false;
            }
        }

        /* Remove Choose address if Salesrep and if not Masquerading */
        if ($block instanceof \Epicor\Lists\Block\Addresses\Link) {
            $customerId = $this->customerSession->getCustomerId();
            $customer = $this->customerCustomer->load($customerId);
            $commHelper = $this->commHelper;
            if ($customer->isSalesRep() && $commHelper->isMasquerading() == false) {
                $block->changeAddressAllowed = false;
            }
        }

        if ($block instanceof \Magento\Framework\View\Element\Html\Links && $block->getTitle() == 'Customer Connect') {
            $helper = $this->commonAccessHelper;
            $cccdActive = $this->scopeConfig->isSetFlag('customerconnect_enabled_messages/CCCD_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $cccsActive = $this->scopeConfig->isSetFlag('customerconnect_enabled_messages/CCCS_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $checkCapsActive =$this->arPaymentsModel->checkArpaymentsActive();
            $customer = $this->customerSession->getCustomer();
            $isCentralCollection = $helper->isCentralCollection($customer);
            if (!$cccsActive || ($this->scopeConfig->isSetFlag('epicor_common/accessrights/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && !$helper->customerHasAccess('Customerconnect', 'Contracts', 'index', '', 'Access'))) {
                $block->removeLinkByName('lists_account_contracts');
            }
             if (!$checkCapsActive || ($checkCapsActive && !$isCentralCollection)) {
                $block->removeLinkByName('customerconnect_account_arpayment');
            }
        }
        if ($block instanceof \Epicor\QuickOrderPad\Block\Link && $block->getTitle() == "Quick Order Pad") {
            $salesRepHelper = $this->salesRepHelper;
            /* @var $salesRepHelper Epicor_SalesRep_Helper_Data */
            $customerSession = $this->customerSession;
            /* @var $customerSession \Magento\Customer\Model\Session */
            $customer = $customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */
            if ($salesRepHelper->isEnabled() && $customer->isSalesRep() && !$salesRepHelper->isMasquerading()) {
                $block->topLinkAllowed = false;
            }
        }

        if ($block instanceof \Epicor\QuickOrderPad\Block\Link && $block->getTitle() == "Quick Order Pad" && $customer->isSupplier()) {
            $block->topLinkAllowed = false;
        }
        if ($block instanceof \Epicor\Lists\Block\Addresses\Link && $customer->isSupplier()) {
            $block->changeAddressAllowed = false;
        }

    }

}
