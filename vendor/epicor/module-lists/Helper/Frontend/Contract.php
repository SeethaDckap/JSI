<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Helper\Frontend;

/**
 * Helper for List Contracts on the frontend
 *
 * @category   Epicor
 * @package    Epicor_List
 * @author     Epicor Websales Team
 */
class Contract extends \Epicor\Lists\Helper\Frontend {

    protected $erpAccount;
    protected $products;
    protected $contractTitles = array();

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Epicor\Lists\Model\ListModel\AddressFactory
     */
    protected $listsListModelAddressFactory;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteQuoteAddressFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $customerSession;
    protected $messageManager;
    protected $itemManager;
    protected $contractsDisabled = null;
    protected $linesCheckExistingProducts = null;
    protected $getSelectedContractCode = null;
    protected $requiredContractType = null;
    protected $contractsEnabled = null;

    public function __construct(
    // FOR PARENT
        \Epicor\Lists\Helper\Context $context,
        \Epicor\Lists\Model\Contract\AddressFactory $listsContractAddressFactory,
        \Epicor\Lists\Model\ListFilterReader $filterReader,
        // FOR THIS CLASS
            \Magento\Checkout\Helper\Cart $checkoutCartHelper,
            \Epicor\Lists\Model\ListModel\AddressFactory $listsListModelAddressFactory,
            \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
            \Magento\Quote\Model\Quote\AddressFactory $quoteQuoteAddressFactory,
            \Magento\Quote\Model\Quote\Item $itemManager
    ) {
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->listsListModelAddressFactory = $listsListModelAddressFactory;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->quoteQuoteAddressFactory = $quoteQuoteAddressFactory;
        $this->customerSession = $context->getCustomerSessionFactory();
        $this->urlEncoder = $context->getUrlEncoder();
        $this->messageManager = $context->getMessageManager();
        $this->itemManager =  $itemManager;
        $listsSessionHelper = $context->getListsSessionHelper();                
        $customerAddressFactory = $context->getCustomerAddressFactory(); 
        parent::__construct(
                $context, $listsContractAddressFactory, $filterReader
        );
    }

    /**
     * Returns whether contracts are enabled
     * 
     * @return boolean
     */
    public function contractsEnabled() {
        if($this->contractsEnabled === null){	
            if ($this->listsEnabled() == false) {
                    $this->contractsEnabled = false;
                    return $this->contractsEnabled;	
            }

			$customerSession = $this->customerSessionFactory->create();
			/* @var $customerSession Mage_Customer_Model_Session */
			$customer = $this->getCustomer();
			/* @var $customer Epicor_Comm_Model_Customer */

			if (
				($customerSession->isLoggedIn() && $customer->getEccErpaccountId()) &&
				in_array($this->allowedContractType(), array('H', 'B'))
			) {
				$this->contractsEnabled = true;
			} else {
				$this->contractsEnabled = false;
			}
		}
		return $this->contractsEnabled;	
    }

    /**
     * Returns whether contracts are disabled
     *
     * @return boolean
     */
    public function contractsDisabled() {
        if($this->contractsDisabled === null){
            $this->contractsEnabled() ? $this->contractsDisabled =  false : $this->contractsDisabled = true;
        }
        return $this->contractsDisabled;
    }

    /**
     * Returns whether line contracts are allowed
     *
     * @return boolean
     */
    public function lineContractsAllowed() {
        if (
                $this->contractsEnabled() &&
                $this->allowedContractType() == 'B'
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets Active Contracts for the current logged in Customer (including contracts)
     *
     * @return array $contracts
     */
    public function getActiveContracts() {
        
        if ($this->contractsDisabled()) {
            return [];
        }
        
        if (is_null($this->contracts)) {
            $this->contracts = array();
            $contractIds = $this->registry->registry('epicor_lists_active_contracts');
            if (is_null($contractIds)) {
                $this->getActiveLists();
                $contractIds = $this->getTypeIds('Co');
                $this->registry->unregister('epicor_lists_active_contracts');
                $this->registry->register('epicor_lists_active_contracts', $contractIds);
            }
            $contracts = array_intersect_key($this->lists, $contractIds);


            if (count($contracts) > 1) {
                $contracts = $this->filterContracts($contracts);
            }
            $this->contracts = $contracts;
        }
        if (is_null($this->contracts)) {
            $this->contracts=[];
        }
        return $this->contracts;
    }

    /**
     * Returns whether products need to filtered by contracts
     *
     * @return integer
     */
    public function mustFilterByContract() {
        if (
                $this->contractsEnabled() &&
                $this->allowNonContractItems() == false
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Sets the given id as the selected contract
     *
     * @param integer $contractId
     */
    public function selectContract($contractId) {
        $transportObject = $this->dataObjectFactory->create();
        $transportObject->setContractId($contractId);

        $this->_eventManager->dispatch('epicor_lists_contract_select_before', array('transport' => $transportObject));

        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        $sessionHelper->setValue('ecc_selected_contract', $transportObject->getContractId());
        $sessionHelper->setValue('ecc_contract_checkout_disabled', false);

        $this->setAddressForContract($contractId);


        $this->_eventManager->dispatch('epicor_lists_contract_select_after', array('transport' => $transportObject));
    }

    /**
     * Returns the selected contract ID
     * 
     * @return integer
     */
    public function getSelectedContract() {
        $sessionHelper = $this->listsSessionHelper;
        return $sessionHelper->getValue('ecc_selected_contract');
    }

    /**
     * Sets the given id as the selected contract
     *
     * @param integer $shipto
     */
    public function selectContractShipto($shipto) {
        if ($this->registry->registry('ecc_contract_allow_change_shipto')) {
            $sessionHelper = $this->listsSessionHelper;
            /* @var $sessionHelper Epicor_Lists_Helper_Session */
            $sessionHelper->setValue('ecc_selected_contract_shipto', $shipto);
            $this->setCartShiptoAddress($shipto);
        }
    }

    /**
     * Sets the cart shipto from an address Code
     * 
     * @param string $addressCode
     */
    public function setCartShiptoAddress($addressCode) {
        $customer = $this->getCustomer();
        $addresses = $customer->getAddressesByType('delivery');

        $shipToAddress = false;
        foreach ($addresses as $address) {
            if ($address->getEccErpAddressCode() == $addressCode) {
                $shipToAddress = $address;
                break;
            }
        }

        if ($shipToAddress) {
            $this->registry->unregister('QuantityValidatorObserver');
            $this->registry->register('QuantityValidatorObserver', 1);
            $quote = $this->checkoutCartFactory->create()->getQuote();
            $this->registry->unregister('QuantityValidatorObserver');
            /* @var $quote Epicor_Comm_Model_Quote */
            $quoteShippingAddress = $this->quoteQuoteAddressFactory->create();
            $quoteShippingAddress->setData($shipToAddress->getData());
            $quoteShippingAddress->setCustomerAddressId($shipToAddress->getId());
            $quote->setShippingAddress($quoteShippingAddress);
            $quote->save();
        }
    }

    /**
     * Returns the selected contract ID
     *
     * @return integer
     */
    public function getSelectedContractShipto() {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */


        return $sessionHelper->getValue('ecc_selected_contract_shipto');
    }

    /**
     * Returns the selected contract ID
     *
     * @return \Epicor\Lists\Model\ListModel\Address
     */
    public function getSelectedContractShiptoModel() {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        $shipto = $sessionHelper->getValue('ecc_selected_contract_shipto');
        $shiptoModel = $this->listsListModelAddressFactory->create();
        /* @var $shiptoModel Epicor_Lists_Model_ListModel_Address */
        if ($shipto) {
            $shiptoModel = $shiptoModel->load($shipto);
        }

        return $shiptoModel;
    }

    /**
     * Returns the selected contract  Model
     * 
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getSelectedContractModel() {
        $selectedContract = $this->getSelectedContract();
        $contracts = $this->getActiveContracts();
        return isset($contracts[$selectedContract]) ? $contracts[$selectedContract] : false;
    }

    /**
     * Returns the selected contract  Model
     * 
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getSelectedContractCode() {
		
        if($this->getSelectedContractCode === null){
            $contract = $this->getSelectedContractModel();
            $contract ? $this->getSelectedContractCode =  $contract->getErpCode() : $this->getSelectedContractCode =  '';
        }
        return $this->getSelectedContractCode;
    }

    /**
     * Checks if the given id is a valid contract
     *
     * @param integer $contractId
     *
     * @return boolean
     */
    public function isValidContractId($contractId) {
        $contracts = $this->getActiveContracts();
        return isset($contracts[$contractId]);
    }

    /**
     * Gets allowed contract type for current session
     *
     * Return Values:
     *
     * H - Header Only
     * B - Both Header & Line
     * N - None
     *
     * return string
     */
    public function allowedContractType() {
        $erpAccount = $this->getSessionErpAccount();
        return $erpAccount->checkAllowedContractType();
    }

    /**
     * Gets required contract type for current session
     *
     * Return Values:
     *
     * H - Header Only
     * E - Either Header or Line
     * O - Optional
     *
     * return string
     */
    public function requiredContractType() {
		
       if($this->requiredContractType === null){
            $erpAccount = $this->getSessionErpAccount();
             if ($erpAccount) {
                $this->requiredContractType = $erpAccount->checkRequiredContractType();
             }
        }
        return $this->requiredContractType;
    }

    /**
     * Gets allow non contract items setting for current session
     *
     * Return Values:
     *
     * 1 - Yes
     * 0 - No
     *
     * return integer
     */
    public function allowNonContractItems() {
        $erpAccount = $this->getSessionErpAccount();
        return $erpAccount ? $erpAccount->checkAllowNonContractItems() : false;
    }

    /**
     * Gets session ERP Account
     * 
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    public function getSessionErpAccount() {
        if (is_null($this->erpAccount)) {
            $erpAccount = $this->registry->registry('ecc_erp_account');
            if (is_null($erpAccount)) {
                $erpAccount = $this->getErpAccountInfo();
                $this->registry->unregister('ecc_erp_account');
                $this->registry->register('ecc_erp_account', $erpAccount);
            }

            $this->erpAccount = $erpAccount;
        }
        return $this->erpAccount;
    }

    /**
     * Gets contract ship to settings for current session
     *
     * @return array
     */
    public function getShipToSettings() {
        $settings = array(
            'enabled' => $this->scopeConfig->isSetFlag('epicor_lists/contracts/shipto', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'shipto_default' => false,
            'shipto_date' => false,
            'shipto_prompt' => false
        );

        if ($settings['enabled']) {
            $customer = $this->getCustomer();
            $customerSettings = $customer->getContractShipToSettings();
            $settings = array_merge($settings, $customerSettings);
        }

        return $settings;
    }

    /**
     * Gets contract ship to settings for current session
     *
     * @return array
     */
    public function getHeaderContractSettings() {
        $settings = array(
            'enabled' => $this->scopeConfig->isSetFlag('epicor_lists/contracts/header', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'header_selection' => false,
            'header_prompt' => false,
            'header_always' => false
        );

        if ($settings['enabled']) {
            $customer = $this->getCustomer();
            $customerSettings = $customer->getContractHeaderSettings();
            $settings = array_merge($settings, $customerSettings);
        }

        return $settings;
    }

    /**
     * Gets contract ship to settings for current session
     *
     * @return array
     */
    public function getLineContractSettings() {
        $settings = array(
            'enabled' => $this->scopeConfig->isSetFlag('epicor_lists/contracts/line', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'line_selection' => false,
            'line_prompt' => false,
            'line_always' => false
        );

        if ($settings['enabled']) {
            $customer = $this->getCustomer();
            $customerSettings = $customer->getContractLineSettings();
            $settings = array_merge($settings, $customerSettings);
        }

        return $settings;
    }

    /**
     * Works out if there is a contract that should be auto selected
     *
     * @return void
     */
    public function autoSelectContract() {
        if ($this->contractsDisabled()) {
            return;
        }

        $contracts = $this->getActiveContracts();

        if (count($contracts) > 0) {
            $this->assignContract($contracts);
        } else {
            $this->noContractCheck();
        }
    }

    /**
     * Checks to see if we need to set a session value to disable the cart
     */
    public function noContractCheck() {
        $contractsOptional = $this->requiredContractType() == 'O';

        if ($contractsOptional == false) {
            $sessionHelper = $this->listsSessionHelper;
            /* @var $sessionHelper Epicor_Lists_Helper_Session */
            $sessionHelper->setValue('ecc_contract_checkout_disabled', true);
        }
    }

    /**
     * Filters contracts based on settings
     * 
     * @param array $contracts
     * @return array
     */
    protected function filterContracts($contracts) {
        $settings = $this->getShipToSettings();

        if ($settings['enabled']) {
            $contracts = $this->filterByShipTo($contracts);
        }

        if (count($contracts) <= 1) {
            return $contracts;
        }

        $header = $this->getHeaderContractSettings();

        if ($header['enabled']) {
            $contracts = $this->filterByHeader($contracts);
        }

        return $contracts;
    }

    /**
     * Filters Active contracts by ship to settings
     * 
     * @param array $contracts
     * @return array
     */
    protected function filterByShipTo($contracts) {
        $settings = $this->getShipToSettings();

        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */

        if (in_array($settings['shipto_default'], array('default', 'specific'))) {
            // filter by default shipto or specified shipto
            if ($settings['shipto_default'] == 'default') {
                $addressCode = $this->getDefaultShipToAddressCode();
            } else {
                $addressCode = $this->getSpecifiedShipto();
            }

            if ($addressCode) {
                $this->selectContractShipto($addressCode);
                return $this->filterByAddressCode($contracts, $addressCode);
            }
        }

        if (in_array($settings['shipto_date'], array('newest', 'oldest'))) {
            // filter by shipto activation date
            switch ($settings['shipto_date']) {
                case 'newest':
                    $filterData = $this->getNewestShipToAddressCode($contracts);
                    break;
                case 'oldest':
                    $filterData = $this->getOldestShipToAddressCode($contracts);
                    break;
            }

            $chosenCode = $this->getSpecifiedShipto();
            if ($chosenCode && isset($filterData['contracts'][$chosenCode])) {
                $contractData = $filterData['contracts'][$chosenCode];
                return array_intersect_key($contracts, array_flip($contractData));
            }

            if (count($filterData['addresses']) == 1) {
                $addressCode = array_pop($filterData['addresses']);
                $sessionHelper->setValue('ecc_shipto_select_filter', array($addressCode));
                $contractData = $filterData['contracts'][$addressCode];
                $this->selectContractShipto($addressCode);
                return array_intersect_key($contracts, array_flip($contractData));
            } else {
                $addressCodes = $filterData['addresses'];
                $sessionHelper->setValue('ecc_shipto_select_filter', $addressCodes);
                if ($settings['shipto_prompt']) {
                    $this->redirectToShiptoSelect();
                } else {
                    return $this->filterByAddressCode($contracts, $addressCodes);
                }
            }
        }

        // no settings used, so filter by all shiptos

        $custShipTos = $this->getCustomerShipto();
        $chosenCode = $this->getSpecifiedShipto();
        if ($chosenCode && in_array($chosenCode, $custShipTos)) {
            return $this->filterByAddressCode($contracts, $custShipTos);
        }

        $sessionHelper->setValue('ecc_shipto_select_filter', $custShipTos);
        if (count($custShipTos) == 1) {
            $addressCode = array_pop($custShipTos);
            $this->selectContractShipto($addressCode);
            return $this->filterByAddressCode($contracts, $addressCode);
        } else if ($settings['shipto_prompt']) {
            $this->redirectToShiptoSelect();
        } else {
            return $this->filterByAddressCode($contracts, $custShipTos);
        }
    }

    /**
     * Returns the customers default address code
     * 
     * @return array()
     */
    public function getCustomerShipto($forceAll = false) {

        $selected = $this->getSelectedContractShipto();
        $addressCodes = array();
        if ($selected === null || $forceAll) {
            $customer = $this->getCustomer();
            $addresses = $customer->getAddressesByType('delivery');

            foreach ($addresses as $address) {
                /* @var $address Epicor_Comm_Model_Customer_Address */
                $addressCodes[] = $address->getEccErpAddressCode();
            }
        } else {
            $addressCodes[] = $selected;
        }

        return $addressCodes;
    }

    /**
     * Returns the customers default address code
     * 
     * @return mixed
     */
    public function getDefaultShipToAddressCode() {
        $customer = $this->getCustomer();
        $address = $customer->getPrimaryShippingAddress();

        if (!$address) {
            return false;
        }

        return $address->getEccErpAddressCode();
    }

    /**
     * Gets specified ship to
     *
     * @return type
     */
    protected function getSpecifiedShipto() {
        $shipto = $this->getSelectedContractShipto() ?: false;

        if (
                $shipto === false ||
                $this->isValidShiptoAddressCode($shipto)
        ) {
            return $shipto;
        }

        return false;
    }

    /**
     * Returns the Newest Ship to Address Code
     *
     * @return string
     */
    public function getNewestShipToAddressCode($contracts) {
        return $this->getShipToByActivationDate('newest', $contracts);
    }

    /**
     * Returns the Newest Ship to Address Code
     *
     * @return string
     */
    public function getOldestShipToAddressCode($contracts) {
        return $this->getShipToByActivationDate('oldest', $contracts);
    }

    /**
     * Gets one or more ship to address codes by activation date
     *
     * @param string $activationFilter
     * @param array $contracts
     * @return array
     */
    protected function getShipToByActivationDate($activationFilter, $contracts) {
        $addresses = $this->listsResourceListModelAddressCollectionFactory->create();
        /* @var $addresses Epicor_Lists_Model_Resource_List_Address_Collection */
        $contractIds = array_keys($contracts);

        $addresses->addFieldToFilter('list_id', array('in' => $contractIds));
        $addresses->filterActive();
        $addresses->filterByActivationDate($activationFilter, $contractIds);

        $addressData = array();
        $contractData = array();
        foreach ($addresses->getItems() as $address) {
            /* @var $address Epicor_Lists_Model_ListModel_Address */
            if (!in_array($address->getAddressCode(), $addressData)) {
                $addressData[$address->getId()] = $address->getAddressCode();
            }
            $contractData[$address->getAddressCode()][] = $address->getListId();
        }

        return array(
            'addresses' => $addressData,
            'contracts' => $contractData,
        );
    }

    /**
     * Filters contracts by Newest Start Date
     *
     * @param array $contracts
     * @param string|array $addressCode
     * @return array
     */
    protected function filterByAddressCode($contracts, $addressCode) {
        $contractsCollection = $this->listsResourceListModelCollectionFactory->create();
        /* @var $contractsCollection Epicor_Lists_Model_Resource_List_Collection */
        $contractsCollection->filterByAddressCode($addressCode);
        $contractsCollection->addFieldToFilter('main_table.id', array('in' => array_keys($contracts)));

        $ids = $contractsCollection->getAllIds();
        $contracts = array_intersect_key($contracts, array_flip($ids));
        return $contracts;
    }

    /**
     * Redirects to Contract Select
     *
     * @param array $contracts
     */
    protected function redirectToShiptoSelect() {
        //M1 > M2 Translation Begin (Rule p2-6.2)
        //$controller = Mage::app()->getFrontController()->getAction();
        $controller = $this->request;
        //M1 > M2 Translation End

        if (stripos($controller->getFullActionName(), 'shipto') === false) {
            
            $error = __('You must select an address before continuing');
            if (stripos($controller->getFullActionName(), 'loginPost')){
                $customerSession = $this->customerSessionFactory->create();
                $customerSession->setContractselectlogin(true);
            }        
            if (stripos($controller->getFullActionName(), 'loginPost') === false &&
                stripos($controller->getFullActionName(), 'section') === false
            ){
                $this->messageManager->addErrorMessage($error);
            }
            //M1 > M2 Translation Begin (Rule p2-3)
            /* Mage::app()->getResponse()->setRedirect(Mage::getUrl('epicor_lists/contract/shipto'));
              die(Mage::app()->getResponse()); */
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->response->setRedirect(Mage::getUrl('epicor_lists/contract/shipto'));
            $this->response->setRedirect($this->_getUrl('epicor_lists/contract/shipto'));
            //M1 > M2 Translation End
            die($this->response->sendResponse());
            //M1 > M2 Translation Begin (Rule p2-3)
            exit();
        }
    }

    /**
     * Filters Active contracts by Date settings
     *
     * @param array $contracts
     * @return array
     */
    protected function filterByHeader($contracts) {
        $header = $this->getHeaderContractSettings();

        switch ($header['header_selection']) {
            case 'newest':
                // get the newest contract(s)
                $contracts = $this->getNewestContracts($contracts);
                break;
            case 'oldest':
                // get the oldest contract(s)
                $contracts = $this->getOldestContracts($contracts);
                break;
            case 'recent':
                // get the most recently used contract (check orders perhaps?)
                $contracts = $this->getMostRecentlyUsedContract($contracts);
                break;
        }

        return $contracts;
    }

    /**
     * Filters contracts by Newest Start Date
     *
     * @param array $contracts
     * @return array
     */
    public function getNewestContracts($contracts) {
        $date = false;
        $filteredContracts = array();
        foreach ($contracts as $contractId => $contract) {
            /* @var $contract Epicor_Lists_Model_ListModel */
            if (!$contract->getStartDate()) {
                continue;
            }

            $contractDate = strtotime($contract->getStartDate());
            if (empty($date) || $contractDate > $date) {
                $date = $contractDate;
                $filteredContracts = array();
                $filteredContracts[$contractId] = $contract;
            } else if ($contractDate == $date) {
                $filteredContracts[$contractId] = $contract;
            }
        }

        return $filteredContracts;
    }

    /**
     * Filters contracts by Oldest Start Date
     *
     * @param array $contracts
     * @return array
     */
    public function getOldestContracts($contracts) {
        $date = false;
        $filteredContracts = array();
        foreach ($contracts as $contractId => $contract) {
            /* @var $contract Epicor_Lists_Model_ListModel */
            if (!$contract->getStartDate()) {
                continue;
            }

            $contractDate = strtotime($contract->getStartDate());
            if (empty($date) || $contractDate < $date) {
                $date = $contractDate;
                $filteredContracts = array();
                $filteredContracts[$contractId] = $contract;
            } else if ($contractDate == $date) {
                $filteredContracts[$contractId] = $contract;
            }
        }

        return $filteredContracts;
    }

    /**
     * Filters contracts by the Most Recently Used Contract
     *
     * @param array $contracts
     * @return array
     */
    public function getMostRecentlyUsedContract($contracts) {
        $date = false;
        $filteredContracts = array();
        foreach ($contracts as $contractId => $contract) {
            $contractModel = $contract->getContract();
            /* @var $contract Epicor_Lists_Model_ListModel */
            if (!$contractModel->getLastUsedTime()) {
                continue;
            }

            $contractDate = strtotime($contractModel->getLastUsedTime());
            if (empty($date) || $contractDate > $date) {
                $date = $contractDate;
                $filteredContracts = array();
                $filteredContracts[$contractId] = $contract;
            } else if ($contractDate == $date) {
                $filteredContracts[$contractId] = $contract;
            }
        }
        return $filteredContracts;
    }

    /**
     * Selects a contract if necessary
     *
     * @param array $contracts
     */
    protected function assignContract($contracts) {
        $required = $this->requiredContractType();
        $header = $this->getHeaderContractSettings();
        $headerRequired = ($header['enabled'] && $header['header_always']);
        if (count($contracts) == 1) {
            if (
                    in_array($required, array('H', 'E')) ||
                    $headerRequired
            ) {
                $contract = array_pop($contracts);
                /* @var $contract Epicor_Lists_Model_Contract */
                $this->selectContract($contract->getId());
            }
        } else {
            if ($required == 'H' || $header['header_prompt']) {
                $this->redirectToSelect(($required == 'H'));
            }
        }
    }

    /**
     * Redirects to Contract Select
     *
     * @param boolean $mandatory
     */
    protected function redirectToSelect($mandatory) {

        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        if (
                ($mandatory || $sessionHelper->getValue('ecc_optional_select_contract_show') !== false)
        ) {
            if ($mandatory) {
                $error = __('You must select a Contract before continuing');
                $customerSession = $this->customerSessionFactory->create();
                if(stripos($this->request->getFullActionName(), 'section') === false && !$customerSession->getContractselectlogin()) {
                    $this->messageManager->addError($error);
                }
                $customerSession->setContractselectlogin(false);
            } else {
                $sessionHelper->setValue('ecc_optional_select_contract_show', true);
            }
            //M1 > M2 Translation Begin (Rule p2-3)
            /* Mage::app()->getResponse()->setRedirect(Mage::getUrl('epicor_lists/contract/select'));
              die(Mage::app()->getResponse()); */
            //M1 > M2 Translation Begin (Rule p2-4)
            //$this->response->setRedirect(Mage::getUrl('epicor_lists/contract/select'));
            $this->response->setRedirect($this->_getUrl('epicor_lists/contract/select'));
            //M1 > M2 Translation End
            die($this->response->sendResponse());
            //M1 > M2 Translation End
        }
    }

    /**
     * Validates that an item can have contract chosen on the cart page
     * 
     * @return boolean
     */
    public function canDisplayCartContracts() {
        $selectedContract = $this->getSelectedContract();
        $allowedContractType = $this->allowedContractType();
        $requiredContractType = $this->requiredContractType();

        $lineSettings = $this->getLineContractSettings();

        $showForOptional = $lineSettings['enabled'] && $lineSettings['line_prompt'];

        if (
                $this->contractsEnabled() &&
                empty($selectedContract) &&
                $allowedContractType == 'B' &&
                ($requiredContractType == 'E' || ($requiredContractType == 'O' && $showForOptional))
        ) {
            return $this->cartHasContractItems();
        } else {
            return false;
        }
    }

    /**
     * Gets contracts dropdown for cart items
     *
     * @param \Epicor\Comm\Model\Quote\Item $item
     *
     * @return string
     */
    public function hideCartItemLinePrice($item) {
        $requiredContractType = $this->requiredContractType();

        $code = $item->getEccContractCode();
        if (
                $this->canDisplayCartContracts() &&
                empty($code) &&
                $requiredContractType == 'E'
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets contracts dropdown for cart items
     *
     * @param \Epicor\Comm\Model\Quote\Item $item
     *
     * @return string
     */
    public function cartItemContractDisplay($item) {
        $contractCode = $item->getEccContractCode();

        $availableContracts = $this->getContractsForCartItem($item);

        $remove = false;
        if (empty($availableContracts)) {
            $display = __('Non-Contract Item');
            $buttonLabel = '';
        } else if (empty($contractCode)) {
            $display = __('No Contract Selected');
            $buttonLabel = __('Select Contract');
        } else {
            $display = $this->getContractTitle($contractCode);
            $buttonLabel = __('Change Contract');
            $remove = true;
        }

        if (empty($buttonLabel) == false) {
            $hideDiv = false;
            if (($buttonLabel == 'Change Contract') && (count($availableContracts) == 1)) {
                $hideDiv = "style='display:none'";
            }
            $buttonHtml = '<div ' . $hideDiv . '><button onclick="javascript:lineContractSelect.openpopup(' . $item->getId() . ');" id="button_select_' . $item->getId() . '" title="' . $buttonLabel . '" type="button" class="button line-contract-select" style="display: inline-block; width: 100%"><span><span>' . $buttonLabel . '</span></span></button></div>';

            if ($remove) {
                $store = $this->storeManager->getStore();
                $returnUrl = $store->getCurrentUrl();
                $params = array(
                    'itemid' => $item->getId(),
                    'contract' => '',
                    'return_url' => base64_encode($this->urlEncoder->encode($returnUrl))
                );
                //M1 > M2 Translation Begin (Rule p2-4)
                //$url = Mage::getUrl('epicor_lists/cart/applycontractselect', $params);
                $url = $this->_getUrl('epicor_lists/cart/applycontractselect', $params);
                //M1 > M2 Translation End
                $buttonLabel = __('Remove Contract');
                $buttonHtml .= '<div><button onclick="javascript:window.location=\'' . $url . '\'" id="button_select_' . $item->getId() . '" title="' . $buttonLabel . '" type="button" class="button line-contract-select" style="display: inline-block; width: 100%"><span><span>' . $buttonLabel . '</span></span></button></div>';
            }


            $display .= $buttonHtml;
        }

        return $display;
    }

    /**
     * Gets contracts for cart item
     *
     * @param \Epicor\Comm\Model\Quote\Item $item
     *
     * @return array
     */
    public function getContractsForCartItem($item) {
        $contractHelper = $this->listsFrontendProductHelper;
        /* @var $contractHelper Epicor_Lists_Model_Frontend_Product */

        $availableContracts = $contractHelper->getContractsForProduct($item->getProductId());
        $filter = $this->getLineContractFilter($item->getId());
        if (is_array($filter)) {
            $lineFiltered = array();
            foreach ($availableContracts as $key => $contract) {
                if (in_array($contract->getErpCode(), $filter)) {
                    $lineFiltered[$key] = $contract;
                }
            }
            $availableContracts = $lineFiltered;
        }

        return $availableContracts;
    }

    /**
     * Checks if a productId is valid for a contract id
     *
     * @param integer $contractId
     * @param integer $productId
     *
     * @return boolean
     */
    public function isProductValidForContract($contractId, $productId) {
        $contractHelper = $this->listsFrontendProductHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Product */

        $availableContracts = $contractHelper->getContractsForProduct($productId);

        return isset($availableContracts[$contractId]);
    }

    /**
     * Gets a collection that returns valid contract addresses for the customer
     *
     * If id is passed, then filtered to single address
     *
     * @param string $addressCode
     *
     * @return \Epicor\Lists\Model\ResourceModel\List\Address\Collection
     */
    public function isValidShiptoAddressCode($addressCode) {
        /* @var $customer Epicor_Comm_Model_Customer */
        $addressCodes = $this->getCustomerShipto(true);

        return in_array($addressCode, $addressCodes);
    }

    /**
     * Gets the title  of a Contract form the given code
     *
     * @param string $contractCode
     *
     * @return string
     */
    public function getContractTitle($contractCode) {
        if (isset($this->contractTitles[$contractCode]) == false) {
            $title = $contractCode;
            $erpAccount = $this->getSessionErpAccount();
            if ($contractCode && $erpAccount) {
                $separator = $this->getUOMSeparator();
                $accountCode = $erpAccount->getAccountNumber() . $separator . $contractCode;
                $contract = $this->listsListModelFactory->create();
                /* @var $contract Epicor_Lists_Model_ListModel */
                $contract->load($accountCode, 'erp_code');
                if ($contract->isObjectNew()) {
                    $shortCode = $erpAccount->getShortCode() . $separator . $contractCode;
                    $contract->load($shortCode, 'erp_code');
                }

                $title = $contract->getTitle() ?: $contractCode;
            }

            $this->_titles[$contractCode] = $title;
        }
        return $this->_titles[$contractCode];
    }

    /**
     * Works out if the customer can checkout or not
     * 
     * @return boolean
     */
    public function stopCheckout() {
        if ($this->contractsDisabled()) {
            return false;
        }

        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        $disabled = $sessionHelper->getValue('ecc_contract_checkout_disabled');
        if ($disabled) {
            return true;
        }

        $required = $this->requiredContractType();

        if ($required == 'O') {
            return $this->checkForContractsCombinations();
        }

        $selected = $this->getSelectedContract();
        if ($required == 'H') {
            return empty($selected);
        }

        if (empty($selected)) {
            // required = E and no contract selected
            $cart = $this->checkoutCartHelper->getCart();
            /* @var $cart Epicor_Comm_Model_Cart */

            $contractMissing = false;

            foreach ($cart->getItems() as $item) {
                $code = $item->getEccContractCode();
                if (empty($code)) {
                    $contractMissing = true;
                    break;
                }
            }

            if ($contractMissing) {
                return $contractMissing;
            }
        }

        // do check here to determine if cart items need addresses

        return $this->checkForContractsCombinations();
    }

    /**
     * Works out if contract combination is correct based on contract addresses
     *
     * @return boolean
     */
    protected function checkForContractsCombinations() {
        $this->registry->unregister('QuantityValidatorObserver');
        $this->registry->register('QuantityValidatorObserver', 1);
        $quote = $this->checkoutCartFactory->create()->getQuote();
        $this->registry->unregister('QuantityValidatorObserver');
        /* @var $quote Epicor_Comm_Model_Quote */

        $cartContracts = $this->getCartContracts($quote);

        if ($quote->hasItems() && $cartContracts) {

            $sessionHelper = $this->listsSessionHelper;
            /* @var $sessionHelper Epicor_Lists_Helper_Session */
            $sessionCodes = $sessionHelper->getValue('ecc_contract_cart_codes');
            if ($sessionCodes == $cartContracts) {
                $contractAddresses = $sessionHelper->getValue('ecc_contract_cart_address_codes');
            } else {
                $contractAddresses = $this->getValidShippingAddressCodesForContracts($cartContracts);
                $sessionHelper->setValue('ecc_contract_cart_address_codes', $contractAddresses);
                $sessionHelper->setValue('ecc_contract_cart_codes', $cartContracts);
            }
            if(empty($contractAddresses)) {
                $contractAddresses = $this->getValidShippingAddressCodesForContracts($cartContracts);
                $sessionHelper->setValue('ecc_contract_cart_address_codes', $contractAddresses);
                $sessionHelper->setValue('ecc_contract_cart_codes', $cartContracts);
            }
            if (empty($contractAddresses)) {
                return __('Checkout disabled as there is no valid shipping address for the combination of items in the cart');
            }
        }

        return false;
    }

    /**
     * Gets any filters for line items
     */
    public function getAllLineContractFilters() {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        return $sessionHelper->getValue('ecc_contract_line_item_filter') ?: array();
    }

    /**
     * Sets any filters for line items
     * 
     * @param array $filters
     */
    public function setAllLineContractFilters($filters) {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        $sessionHelper->setValue('ecc_contract_line_item_filter', $filters);
    }

    /**
     * Gets any filters for a line item
     * 
     * @param integer $itemId
     * 
     * @return boolean / array
     */
    public function getLineContractFilter($itemId) {
        $filters = $this->getAllLineContractFilters();

        if (isset($filters[$itemId])) {
            return $filters[$itemId];
        } else {
            return false;
        }
    }

    /**
     * Sets filters for line item contracts
     * 
     * @param integer $itemId
     * @param array $contracts
     */
    public function setLineContractFilter($itemId, $contracts) {
        $filters = $this->getAllLineContractFilters();
        $filters[$itemId] = $contracts;
        $this->setAllLineContractFilters($filters);
    }

    /**
     * Gets all contract codes form the cart
     * 
     * @param \Epicor\Comm\Model\Quote $quote
     * 
     * @return array     
     */
    public function getQuoteContracts($quote) {
        $contracts = array();

        if ($quote->getEccContractCode()) {
            $contracts[] = $quote->getEccContractCode();
        }

        foreach ($quote->getAllItems() as $item) {
            /* @var $item Epicor_Comm_Model_Quote_Item */
            if ($item->getEccContractCode()) {
                $contracts[] = $item->getEccContractCode();
            }
        }

        return $contracts;
    }

    /**
     * Takes an array of contract codes and returns an array of valid 
     * customer address codes for those contracts
     * 
     * Address codes must be an intersection of addresses
     * 
     * @param array $contracts
     */
    public function getValidShippingAddressCodesForContracts($contracts) {
        $activeContracts = $this->getActiveContracts();

        /* @var $addresses Epicor_Lists_Model_Resource_List_Address_Collection */
        $contractIds = array();
        foreach ($activeContracts as $contract) {
            if (in_array($contract->getErpCode(), $contracts)) {
                $contractIds[] = $contract->getId();
            }
        }


        $addresses = $this->listsResourceListModelAddressCollectionFactory->create();
        /* @var $addresses Epicor_Lists_Model_Resource_List_Address_Collection */
        $addresses->addFieldToSelect('address_code');
        $addresses->getSelect()->columns('count(list_id) as lists_count');
        $addresses->addFieldToFilter('list_id', array('in' => $contractIds));
        $addresses->filterActive();
        $addresses->getSelect()->group('address_code');
        $addresses->getSelect()->having('lists_count = ' . count($contractIds));

        return $addresses->getColumnValues('address_code');
    }

    /**
     * Returns the ecc_contract_line_checked_existing session value and if false set is to true
     * 
     * @return bool
     */
    public function linesCheckExistingProducts() {
        if($this->linesCheckExistingProducts === null){			
            $sessionHelper = $this->listsSessionHelper;
            /* @var $sessionHelper Epicor_Lists_Helper_Session */
            $lineChecked = $sessionHelper->getValue('ecc_contract_line_checked_existing');
            if (!$lineChecked) {
                    $sessionHelper->setValue('ecc_contract_line_checked_existing', true);
            }
            $this->linesCheckExistingProducts = false;
        }
        return $this->linesCheckExistingProducts;
    }

    /**
     * Resets the ecc_contract_line_checked_existing session value to false
     */
    public function resetLinesCheckExistingProducts() {
        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        $sessionHelper->setValue('ecc_contract_line_checked_existing', false);
    }

    /**
     * Checks if anything needs to be done with the line contract information
     * 
     * @param \Epicor\Comm\Model\Quote $quote
     * @param \Epicor\Comm\Model\Product $product
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    public function lineContractCheck(&$quote, &$product, &$item, $checkForExisting = false) {
        $productHelper = $this->commProductHelper;
        /* @var $productHelper Epicor_Comm_Helper_Product */

        $settings = $this->getLineContractSettings();

        if ($product->getEccMsqContractCode()) {
            $item->setEccContractCode($product->getEccMsqContractCode());
        }

        if (
                $this->lineContractsAllowed() == false ||
                $settings['enabled'] == false ||
                $settings['line_selection'] == 'all' ||
                $quote->getEccContractCode() || (
                $checkForExisting == false && (
                $item->getEccContractCode() ||
                $item->isObjectNew() == false
                )
                )
        ) {
            return;
        }

        $contracts = $this->getLineContractByCost($item->getQty(), $product, $settings['line_selection']);

        if (empty($contracts)) {
            return;
        }

        $requiredType = $this->requiredContractType();
        $lineAlways = $settings['line_always'];

        if (count($contracts) > 0) {
            $lineContracts = array();
            foreach ($contracts as $contract) {
                $lineContracts[] = $contract->getContractCode();
            }

            $this->setLineContractFilter($item->getId(), $lineContracts);
        }

        if (
                count($contracts) == 1 &&
                ($requiredType == 'E' || ($requiredType == 'O' && $lineAlways))
        ) {
            $contract = array_pop($contracts);
            $item->setEccContractCode($contract->getContractCode());
            $productHelper->setProductToMsqPrices($product, $contract);
            $productHelper->setProductToMsqContractStock($product, $contract);
        }
    }

    /**
     * Works out the highest / lowest cost contract
     *
     * @param \Epicor\Comm\Model\Product $product
     * @param string $order
     * 
     * @return array
     */
    protected function getLineContractByCost($qty, &$product, $type) {
        $contractData = $product->getEccMsqContractData();

        if (empty($contractData)) {
            return array();
        }

        $highestCost = false;
        $lowestCost = false;
        $contracts = array(
            'highest' => array(),
            'lowest' => array(),
        );

        foreach ($contractData as $contract) {
            /* @var $contract Epicor_Common_Model_Xmlvarien */
            $price = $contract->getCustomerPrice();
            if ($contract->getBreaks() && $contract->getBreaks()->getBreak()) {
                $contract->getBreaks()->getasarrayBreak();
                foreach ($break as $break) {
                    $bQty = $break->getQuantity();
                    if ($qty >= $bQty) {
                        $price = $break->getPrice();
                    } else {
                        break;
                    }
                }
            }

            if ($highestCost === false || $price > $highestCost) {
                $highestCost = $price;
                $contracts['highest'] = array();
                $contracts['highest'][] = $contract;
            } else if ($price == $highestCost) {
                $contracts['highest'][] = $contract;
            }

            if ($lowestCost === false || $price < $lowestCost) {
                $lowestCost = $price;
                $contracts['lowest'] = array();
                $contracts['lowest'][] = $contract;
            } else if ($price == $lowestCost) {
                $contracts['lowest'][] = $contract;
            }
        }

        return $contracts[$type];
    }

    public function setAddressForContract($contractId) {
        // see if address code has been set by address selection
        $selectedContractShipTo = $this->getSelectedContractShipto();
        $addresses = $this->getAddressesForContract($contractId);
        if (!empty($addresses)) {
            //if any contract address is default for the customer, use that
            if (
                    empty($selectedContractShipTo) == false &&
                    isset($addresses['codes']) &&
                    in_array($selectedContractShipTo, $addresses['codes'])
            ) {
                $setShipto = $selectedContractShipTo;
            } else if (
                    isset($addresses['default'])
            ) {
                $setShipto = $addresses['default']['ecc_erp_address_code'];
            } else {
                $setShipto = $addresses[0]['ecc_erp_address_code'];
            }

            $this->setCartShiptoAddress($setShipto);
        }
    }

    public function getAddressesForContract($contractId) {
        $contract = $this->listsListModelFactory->create()->load($contractId);
        $contractErpCode = array($contract->getErpCode());
        $customer = $this->customerSession->create()->getCustomer();
        /* @var $customerSession Mage_Customer_Model_Session */
        $defaultDeliveryAddress = $customer->getDefaultShippingAddress();
        /* @var $customer Epicor_Comm_Model_Customer */
        $customerAddresses = $customer->getAddressesByType('delivery');
        $contractAddresses = $this->getValidShippingAddressCodesForContracts($contractErpCode);

        $filteredAddresses = array();
        foreach ($customerAddresses as $address) {
            if (in_array($address->getEccErpAddressCode(), $contractAddresses)) {
                if (
                        $defaultDeliveryAddress &&
                        $defaultDeliveryAddress->getEccErpAddressCode() == $address->getEccErpAddressCode()
                ) {
                    $filteredAddresses['default'] = $address;
                }
                $filteredAddresses['codes'][] = $address->getEccErpAddressCode();
                $filteredAddresses[] = $address;
            }
        }

        return $filteredAddresses;
    }

    /**
     * Works out if items in the cart are all non-contract or not 
     *    
     * @return boolean
     */
    protected function cartHasContractItems() {

        if (!$this->registry->registry('cartHasContractItems')) {
            $this->registry->unregister('QuantityValidatorObserver');
            $this->registry->register('QuantityValidatorObserver', 1);
            $itemsInCart = $this->checkoutCartFactory->create()->getQuote()->getAllVisibleItems();
            $this->registry->unregister('QuantityValidatorObserver');
            $itemsInCartKeys = array();
            $contracts;
            foreach ($itemsInCart as $item) {
                $contracts = $this->getContractsForCartItem($item);
                if ($contracts) {
                    $this->registry->register('cartHasContractItems', empty($contracts) ? 'no' : 'yes');
                    return true;
                }
            }
            $this->registry->register('cartHasContractItems', empty($contracts) ? 'no' : 'yes');
        }
        return $this->registry->registry('cartHasContractItems') == 'no' ? false : true;
    }
    
    public function getContractCodeByItem($itemId){
        
        $quoteItem = $this->itemManager->load($itemId);
        return $quoteItem->getEccContractCode();
    }
    
    /**
     * Retrive Contract id from contract Code
     */
    public function retrieveContractId($contractCode) 
    {
        if (!$this->erpAccount) {
            $this->erpAccount = $this->getErpAccountInfo();
        }
        
        $contractModel = $this->listsListModelFactory->create()->load($this->erpAccount->getAccountNumber() . $this->commMessagingHelper->getUOMSeparator() . $contractCode,'erp_code');
        return $contractModel->isObjectNew() ? null : $contractModel->getId();
    }

    /*
     * filter out contracts assigned to the quote or quote items that are no longer available
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function getCartContracts(\Magento\Quote\Model\Quote $quote)
    {
        $cartContracts = $this->getQuoteContracts($quote);
        $activeContracts = $this->getActiveContracts();
        $activeContractCodes = [];
        foreach ($activeContracts as $contract) {
            $activeContractCodes[] = $contract->getErpCode();
        }

        $cartContracts = array_unique($cartContracts);
        if ($cartContracts) {
            $contractsNotAvailableButSelected = array_diff_key(array_flip($cartContracts), array_flip($activeContractCodes));
            //remove contracts from quote or items if not available
            if ($contractsNotAvailableButSelected) {
                foreach ($contractsNotAvailableButSelected as $oldContract => $value) {
                    $this->contractsNotAvailableButSelected($quote, $oldContract);
                }
                $quote->save();
                unset($cartContracts[$oldContract]);
            }
        }

        return $cartContracts;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $oldContract
     */
    protected function contractsNotAvailableButSelected(\Magento\Quote\Model\Quote $quote , $oldContract)
    {
        //check if non available contract is assigned to quote or items
        if ($quote->getEccContractCode() == $oldContract) {
            $quote->setEccContractCode(null);
        }

        foreach ($quote->getAllItems() as $item) {
            /* @var $item Epicor_Comm_Model_Quote_Item */
            if ($item->getEccContractCode() == $oldContract) {
                $item->setEccContractCode(null);
            }
        }
    }
}
