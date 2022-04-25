<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Dashboard;


class ErpSelector extends \Epicor\Comm\Block\Customer\Info
{

    protected $_erpAccounts;

    /**
     * @var \Epicor\SalesRep\Helper\Data
     */
    protected $salesRepHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = [])
    {
        $this->salesRepHelper = $salesRepHelper;
        $this->registry = $registry;
        $this->commHelper = $commHelper;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();

        $this->setTitle(__('Account Selector'));
        $this->setTemplate('Epicor_SalesRep::epicor/salesrep/account/dashboard/erp_selector.phtml');
        $this->setColumnCount(1);
        $this->setOnRight(false);
    }

    public function isMasquerading()
    {
        $helper = $this->salesRepHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Data */
        return $helper->isMasquerading();
    }

    public function isMasqueradeAccount($erpAccount)
    {
        $helper = $this->salesRepHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Data */
        $currentErpAccount = $helper->getErpAccountInfo();

        return $currentErpAccount->getId() == $erpAccount->getId();
    }

    public function getErpAccounts($noChild = null)
    {
        if (!$this->_erpAccounts) {
            $account = $this->registry->registry('sales_rep_account');
            /* @var $account \Epicor\SalesRep\Model\Account */
            $erpAccount = array();
            if ($noChild) {
                $childStores = $account->getStoreMasqueradeAccountsNoChild();
            } else {
                $childStores = $account->getStoreMasqueradeAccounts();
            }
            $erpaccount = false;
            foreach ($childStores as $account) {
                $erpaccount[$account->getName() . $account->getId()] = $account;    // save account using name as a key 
            }
            if($erpaccount){
                ksort($erpaccount);                                                     // sort the erpaccount array by key
                foreach ($erpaccount as $erp) {
                    $this->_erpAccounts[$erp->getEntityId()] = $erp;                   // loop through the erpaccount list and use entity id as key to populate erpAccounts 
                }
            }else{
               $this->_erpAccounts = [];
            }
        }
        return $this->_erpAccounts;
    }

    public function getCounts()
    {
        $account = $this->registry->registry('sales_rep_account');
        /* @var $account \Epicor\SalesRep\Model\Account */
        return count($account->getErpAccountIds());
    }

    public function getActionUrl()
    {
        return $this->getUrl('epicor_comm/masquerade/masquerade');
    }

    public function getReturnUrl()
    {
        //$url = $this->getUrl('customer/account/index');
        $url = $this->getUrl('salesrep/account/index');
        return $this->commHelper->getUrlEncoder()->encode($url);
    }

    public function displaySearchButton()
    {
        $display = false;
        if (count($this->_erpAccounts) >= $this->scopeConfig->getValue('epicor_salesrep/general/masquerade_search', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $display = true;
        }
        return $display;
    }
    //M1 > M2 Translation Begin (Rule p2-5.11)
    public function getStoreConfigFlag($path)
    {
        return $this->scopeConfig->isSetFlag($path);
    }
    //M1 > M2 Translation End

}
