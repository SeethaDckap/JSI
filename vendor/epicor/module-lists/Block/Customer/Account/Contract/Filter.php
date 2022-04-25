<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Contract;


/**
 *  Contract Filter block for lists
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Filter extends \Magento\Framework\View\Element\Template
{

    /**
     *
     * @var \Epicor\Comm\Model\Customer\Erpaccount 
     */
    private $_erpAccount;
    protected $_displayDefaultContractFilters;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Lists\Helper\Data $listsHelper,
        array $data = []
    ) {
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        $this->commHelper = $commHelper;
        $this->customerSession = $customerSession;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->listsHelper = $listsHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTitle(__('Contract Filter'));
    }

    /**
     * Get Contract Helper
     * @return \Epicor\Lists\Helper\Frontend\Contract
     */
    public function getContractHelper()
    {
        if (!$this->_contractHelper) {
            $this->_contractHelper = $this->listsFrontendContractHelper;
        }
        return $this->_contractHelper;
    }

    public function getActualAccount()
    {
        $commHelper = $this->commHelper;
        if (is_null($this->_erpAccount)) {
            $this->_erpAccount = $commHelper->getErpAccountInfo(null, 'customer', null, false);
        }
        return $this->_erpAccount;
    }

    public function isAllowed()
    {
        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */
        $customer = $customerSession->getCustomer();
        $allowed = false;
        if ($customer->getId()) {
            $allowed = true;
        }
        return $allowed;
    }

    public function getFormUrl()
    {
        return $this->getUrl('epicor_lists/contract/filter');
    }

    /**
     * Checks config to see if user can choose single or multiple contracts
     * 
     * @return boolean
     */
    public function canChooseMultipleContracts()
    {
        return true;
    }

    public function getReturnUrl()
    {
        $url = $this->frameworkHelperDataHelper->getCurrentUrl();
        return $this->listsHelper->urlEncode($url);
    }

    /**
     * Get session customer allowed contracts
     * 
     * @return array
     */
    public function getCustomerAllowedContracts()
    {
        $contracts = $this->getContractHelper()->getActiveContracts();
        if (!is_array($contracts)) {
            $contracts = array();
        }
        return $contracts;
    }

    public function getSelectedFilterContracts()
    {
        $customerSession = $this->customerSession->getCustomer();
        $eccContractsFilter = $customerSession->getEccContractsFilter();
        return $eccContractsFilter;
    }

    /**
     * 
     * @param string $code
     * 
     * @return boolean
     */
    public function isDefaultFilterSelected($code)
    {
        return in_array($code, $this->getCustomerDefaultFilterCodes());
    }

    public function getCustomerDefaultFilterCodes($codes = false)
    {
        $session = $this->customerSession->getCustomer();
        /* @var $session Mage_Customer_Model_Session */
        if ($session->getEccContractsFilter()) {
            $_displayDefaultContractFilters = $session->getEccContractsFilter();
        }
        return $codes ? array_keys($_displayDefaultContractFilters) : $_displayDefaultContractFilters;
    }

}
