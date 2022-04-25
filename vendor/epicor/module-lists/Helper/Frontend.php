<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Helper;

use Epicor\Lists\CustomerData\ListData;

/**
 * Helper for Lists on the frontend
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Frontend extends \Epicor\Lists\Helper\Data
{

    /**
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     *
     * @var \Epicor\Lists\Model\ListModel
     */
    protected $sessionList;

    /**
     *
     * @var \Epicor\Comm\Model\Customer
     */
    protected $customer;
    protected $lists;
    protected $contracts;
    protected $quickOrderPadLists;
    protected $typeFilter;
    protected $typeReg;

    /**
     * @var \Epicor\Lists\Model\Contract\AddressFactory
     */
    protected $listsContractAddressFactory;

    /**
     * @var \Epicor\Lists\Helper\Session
     */
    protected $listsSessionHelper;

    /**
     * @var \Epicor\Lists\Model\ListFilterReader
     */
    protected $listFilterReader;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var ListData
     */
    private $listData;

    /**
     * Frontend constructor.
     * @param Context $context
     * @param \Epicor\Lists\Model\Contract\AddressFactory $listsContractAddressFactory
     * @param \Epicor\Lists\Model\ListFilterReader $filterReader
     */
    public function __construct(
        \Epicor\Lists\Helper\Context $context,
        \Epicor\Lists\Model\Contract\AddressFactory $listsContractAddressFactory,
        \Epicor\Lists\Model\ListFilterReader $filterReader
    )
    {
        $this->listsContractAddressFactory = $listsContractAddressFactory;

        $listsSessionHelper = $context->getListsSessionHelper();
        $customerAddressFactory = $context->getCustomerAddressFactory();
        $this->listFilterReader = $filterReader;
        $this->listData = $context->getListData();

        parent::__construct(
            $context
        );
    }

    /**
     * Returns whether lists is enabled
     *
     * @return boolean
     */
    public function listsEnabled()
    {
        return $this->scopeConfig->isSetFlag('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Returns whether lists is disabled
     *
     * @return boolean
     */
    public function listsDisabled()
    {
        return $this->scopeConfig->isSetFlag('epicor_lists/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == false;
    }

    /**
     * Gets Active Lists for the current logged in Customer (including contracts)
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param string $file
     * @return array $errors
     */
    public function getActiveLists()
    {
        if (is_null($this->lists)) {
            $this->lists = array();

            $lists = $this->registry->registry('epicor_lists_active_lists');
            if (is_null($lists)) {
                $this->session = $this->customerSessionFactory->create();
                $this->customer = $this->session->getCustomer();
                /* @var $customer  */
                $this->loadLists();
                $this->registry->unregister('epicor_lists_active_lists');
                $this->registry->register('epicor_lists_active_lists', $this->lists);
            } else {
                $this->lists = $lists;
            }
        }

        $this->registerTypes($this->lists);

        return $this->lists;
    }

    /**
     * Gets the IDs of lists of a given type
     *
     * @param string $type
     */
    protected function getTypeIds($type)
    {
        return isset($this->typeReg[$type]) ? $this->typeReg[$type] : array();
    }

    /**
     * Loads Current Active lists collection with filters
     */
    protected function loadLists()
    {
        $collection = $this->listsResourceListModelCollectionFactory->create();
        /* @var $collection Epicor_Lists_Model_Resource_List_Collection */

        $collection->filterActive();

        $filters = $this->getListFilters();
        foreach ($filters as $filter) {
            if ($this->session->getIsPunchout()) {
                $filter->setErpAccountId($this->customer->getEccErpaccountId());
                $filter->setCustomerId($this->customer->getId());
                $filter->setStoreGroupId($this->customer->getWebsiteId());
            }

            $filter->filter($collection);
        }
        $collection->getSelect()->group('main_table.id');
        $this->lists = $collection->getItems();
    }

    /**
     * Gets configured list filters
     *
     * @return array
     */
    public function getListFilters()
    {
        $filterModels = array();
        //M1 > M2 Translation Begin (Rule P2-5.6)
        //$filters = (array) Mage::getConfig()->getNode('frontend/list_filters/filters');
        $filters = (array) $this->globalConfig->get('list_filters/filters');
        //M1 > M2 Translation End


        foreach ($filters as $key => $filter) {
            $filter = (array) $filter;
            //M1 > M2 Translation Begin (Rule 46)
            //$filterModel = Mage::getModel($filter['model']);
            $filterModel = $this->listFilterReader->getFilter($filter['model']);
            //M1 > M2 Translation End
            if ($filterModel instanceof \Epicor\Lists\Model\ListModel\Filter\AbstractModel) {
                $filterModels[] = $filterModel;
            }
        }

        return $filterModels;
    }

    /**
     * Adds an array of lists to the class array, preventing duplicates
     *
     * @param array $lists
     */
    protected function registerTypes($lists)
    {
        $this->typeReg = array();
        foreach ($lists as $list) {
            /* @var $list Epicor_Lists_Model_ListModel */
            $this->typeReg[$list->getType()][$list->getId()] = $list->getId();
        }
    }

    /**
     * Works out if there is a list that should be selected
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getListToSelect()
    {
        $scores = array();
        $lists = $this->getActiveLists();
        foreach ($lists as $list) {
            /* @var $list Epicor_Lists_Model_ListModel */
            $score = $this->scoreList($list);
            if ($score > 0) {
                // Note: this will overwrite previous lists with matching priority
                // this is intended behaviour
                $scores[$score][$list->getPriority()] = $list;
            }
        }

        if (empty($scores)) {
            return false;
        }

        ksort($scores);
        $top = array_pop($scores);
        ksort($top);
        $topList = array_pop($top);

        return $topList;
    }

    /**
     * Works out the score of a list
     *
     * @param \Epicor\Lists\Model\ListModel $list
     */
    protected function scoreList($list)
    {
        $score = 0;
        $listSettings = $list->getSettings();
        if (in_array('M', $listSettings)) {
            $score += 10;
        }

        if (in_array('F', $listSettings)) {
            $score += 5;
        }

        if (in_array('D', $listSettings)) {
            $score += 1;
        }

        return $score;
    }

    /**
     * Checks whether the provided list is still valid for the current session
     *
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @return boolean
     */
    public function isListValid(\Epicor\Lists\Model\ListModel $list)
    {
        return $this->getValidListById($list->getId()) ? true : false;
    }

    /**
     * Looks for the list by Id, if valid returns list, if not returns false
     *
     * @param int $listId
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getValidListById($listId)
    {
        $this->getActiveLists();

        if (isset($this->lists[$listId])) {
            return $this->lists[$listId];
        }

        return false;
    }

    /**
     * Loads Current Customer Specific Contract Address
     */
    public function customerAddresses($addressId)
    {
        $contractAddress = $this->listsContractAddressFactory->create()->getCustomerAddresses($addressId);
        return $contractAddress;
    }

    /**
     * Works out whether the list passes has greater priority than the current default
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param \Epicor\Lists\Model\ListModel $defaultList
     * @return boolean
     * Looks for the Quick Order Pad List with hightest priority
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    protected function shouldListBeDefaultQop($list, $defaultList)
    {
        if ($list->hasSetting('Q') == false) {
            return false;
        }

        if (!$defaultList) {
            return true;
        }

        if ($list->getPriority() > $defaultList->getPriority()) {
            return true;
        } else if ($list->getPriority() == $defaultList->getPriority()) {
            if ($defaultList->hasSetting('D') == $list->hasSetting('D')) {
                return $this->isListNewerThanDefault($list, $defaultList);
            } else {
                return $list->hasSetting('D');
            }
        }

        return false;
    }

    /**
     * Works out whether the list passes has greater priority than the current default
     *
     * @param \Epicor\Lists\Model\ListModel $list
     * @param \Epicor\Lists\Model\ListModel $defaultList
     * @return boolean
     */
    public function isListNewerThanDefault($list, $defaultList)
    {
        $listCreatedDate = strtotime($list->getCreatedDate());
        $defaultCreatedDate = strtotime($defaultList->getCreatedDate());
        if ($listCreatedDate > $defaultCreatedDate) {
            return true;
        } else if ($listCreatedDate == $defaultCreatedDate) {
            return ($list->getId() > $defaultList->getId());
        }

        return false;
    }


    /**
     * Saves the list id value as the session list id
     *
     * @param int $listId
     * @return \Epicor\Lists\Helper\Frontend
     */
    public function setSessionList($listId)
    {
        if ($listId instanceof \Magento\Framework\DataObject) {
            $listId = $listId->getId();
        }

        $sessionHelper = $this->listsSessionHelper;
        /* @var $sessionHelper Epicor_Lists_Helper_Session */
        $sessionHelper->setValue('ecc_quickorderpad_list', $listId);
        $this->sessionList = $this->getValidListById($listId);

        return $this;
    }

    /**
     * To check whether the url is secure or not for AJAX calls
     *
     * @param ($_SERVER['HTTPS'])
     * @return boolean
     */
    public function issecure()
    {
        $params = array();
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $params = array(
                '_secure' => true
            );
        } else {
            $params = array(
                '_secure' => false
            );
        }
        return $params;
    }

    /**
     * To shipping address from address id
     *
     * @param $addressId
     * @return array
     */
    public function getShippingAddress($addressId)
    {
        $addressArray = $this->customerAddressFactory->create()->load($addressId);
        $selectedAddress = $addressArray->getData();
        return $selectedAddress;
    }

    /**
     * To get all cart items
     * @return array
     */
    public function getCartItems()
    {
        $quote = $this->checkoutSession->getQuoteOnly();
        $cartItems = $quote->getAllVisibleItems();
        $productId = array();
        foreach ($cartItems as $item) {
            $productId[] = $item->getProductId();
        }
        return $productId;
    }

    /**
     * Gets active list for FPC
     * @return mixed|string|void|null
     */
    public function getEscapedActiveLists()
    {
        if (!$this->listsEnabled()) return;
        $listsTags = $this->listData->getListTags();
        if (is_null($listsTags)) {
            $lists = $this->getActiveLists();
            $listsTags = [];
            foreach ($lists as $list) {
                $listsTags[] = strtolower($list->getType()) . "_" . $list->getId();
            }
            $selectedContract = $this->listsFrontendContractHelper->getSelectedContract();
            if ($selectedContract) {
                $listsTags[] = "selected_co_" . $selectedContract;
            }
            $listsTags = implode(",", $listsTags);
            $this->listData->setListTags($listsTags);
        }
        return $listsTags;
    }

}
