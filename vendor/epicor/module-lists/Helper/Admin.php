<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Helper;


class Admin extends \Epicor\Comm\Helper\Data
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $listsTypeReader;

    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Epicor\Lists\Model\ListsTypeModelReader $typeReader,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->listsTypeReader = $typeReader;
        parent::__construct($context);
    }

    /**
     * @return \Epicor\Common\Model\MessageUploadModelReader
     */
    public function getListsTypeModelReader()
    {
        return $this->listsTypeReader;
    }
    /**
     * Checks whether lists can be assigned to given ERP accounts
     * 
     * @param array $listIds
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * 
     * @return array
     */
    public function assignErpAccountListsCheck($listIds, $erpAccount)
    {
        $newLists = $listsCollection = $this->listsResourceListModelCollectionFactory->create();
        $newLists->addFieldtoFilter('id', array('in' => $listIds));

        $return = array();

        $accountType = $erpAccount->getAccountType();
        $changeLinkTypes = array();

        foreach ($newLists as $newList) {
            $linkType = $newList->getErpAccountLinkType();
            if (
                (in_array($linkType, array('E', 'N'))) ||
                (($accountType == "B2C") && ($linkType == 'C')) ||
                (($accountType == "B2B") && ($linkType == 'B'))
            ) {
                $return['success']['values'][] = $newList->getId();
                if ($linkType == 'N') {
                    $changeLinkTypes[] = $newList->getId();
                }
            } else {
                $return['error']['values'][] = $newList->getId();
            }
        }

        if ($changeLinkTypes) {
            $this->massUpdateLinkTypes($changeLinkTypes, 'E');
        }
        /* To avoid undefined index notice */
        if (isset($return['error']['values'])) {
            $errorIds = rtrim(implode(',', $return['error']['values']), ',');
            $return['error']['id'] = $errorIds;
        }
        if (isset($return['success']['values'])) {
            $successIds = rtrim(implode(',', $return['success']['values']), ',');
            $return['success']['id'] = $successIds;
        }

        return $return;
    }

    /**
     * Checks whether lists can be assigned to correct customers ERP accounts
     * 
     * @param array $listIds
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * 
     * @return array
     */
    public function assignCustomerAccountListsCheck($listIds, $customer)
    {
        $newLists = $listsCollection = $this->listsResourceListModelCollectionFactory->create();
        /* @var $listsCollection Epicor_Lists_Model_Resource_List_Collection */
        $newLists->addFieldtoFilter('id', array('in' => $listIds));

        $customerAccountType = $customer->getEccErpAccountType();

        $return = array();

        $changeLinkTypes = array();

        $notAllowedTypes = array('guest', 'salesrep');

        $erpAccountType = false;

        if (!in_array($customerAccountType, $notAllowedTypes)) {
            $account = $this->commCustomerErpaccountFactory->create()->load($customer->getEccErpaccountId());
            /* @var $account Epicor_Comm_Model_Customer_Erpaccount */
            $erpAccountType = $account->getAccountType();
            $erpaccountId = $customer->getEccErpaccountId();
        }

        $return = array();
        foreach ($newLists as $newList) {
            /* @var $newList Epicor_Lists_Model_ListModel */
            $linkType = $newList->getErpAccountLinkType();
            $erpIds = $this->erpAccountIds($newList->getId());
            $isValid = true;

            // NO ERP Accounts Selected
            if (empty($erpIds)) {
                // NO ERP Accounts Selected, then ERP Account type Must Match
                if (in_array($linkType, array('C', 'B'))) {
                    $expectedType = $linkType == 'B' ? 'B2B' : 'B2C';
                    $isValid = ($erpAccountType == $expectedType);
                }
            } else {
                // ERP Account(s) selected, so Customer ERP Account ID MUST be selected
                // ECC should NOT assign the customer to that list because
                // the customer belongs to ERP Account '777' and you attempted to
                // assign to a list who is associated with '999' and '888'    
                if (isset($erpaccountId)) {
                    if ((!in_array($erpaccountId, $erpIds))) {
                        $isValid = false;
                    }
                } else {
                    //If the customer is a guest/salesrep then it shouldn't get added to B2B and B2C Erp account types
                    //if Chosen ERP and you have actually selected some ERP accounts then guests no 
                    //because (apart from sales reps) - you can only assign customers that belong to the chosen ERP
                    if ((in_array($linkType, array('C', 'B'))) || ($customerAccountType == "guest")) {
                        $expectedType = $linkType == 'B' ? 'B2B' : 'B2C';
                        $isValid = ($erpAccountType == $expectedType);
                    }
                }
            }

            if ($isValid) {
                $return['success']['values'][] = $newList->getId();
            } else {
                $return['error']['values'][] = $newList->getId();
            }
        }

        $errorIds = "";
        if (isset($return['error']['values'])) {
            $errorIds = rtrim(implode(',', $return['error']['values']), ',');
        }
        $successIds = "";
        if (isset($return['success']['values'])) {
            $successIds = rtrim(implode(',', $return['success']['values']), ',');
        }

        $return['error']['id'] = $errorIds;
        $return['success']['id'] = $successIds;
        return $return;
    }

    /**
     * Get all the erpId's for a particular list Id 
     */
    public function erpAccountIds($listId)
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_ResourceCustomer_Erpaccount */
        $collection->addFieldtoSelect('entity_id');
        $collection->getSelect()->join(
            array('list' => $collection->getTable('ecc_list_erp_account')), 'main_table.entity_id = list.erp_account_id AND list.list_id = "' . $listId . '"', array()
        );

        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item->getId();
        }

        return $items;
    }

    /**
     * Updates given lists to the given link type
     * 
     * @param array $listIds
     * @param string $newType
     */
    private function massUpdateLinkTypes($listIds, $newType)
    {
        $ids = implode(',', $listIds);
        $resource = $this->resourceConnection;
        /* @var $resource Mage_Core_Model_Resource */
        $tableName = $resource->getTableName('ecc_list');

        $query = 'UPDATE ' . $tableName . ' SET erp_account_link_type = "' . $newType . '" WHERE id IN(' . $ids . ')';

        $writeConnection = $resource->getConnection('core_write');
        /* @var $writeConnection ClassName */
        $writeConnection->query($query);
    }

    /**
     * Updates given contracts to Inactive
     * 
     * @param array $listIds
     */
    public function massUpdateListContracts($listIds)
    {

        $resource = $this->resourceConnection;
        /* @var $resource Mage_Core_Model_Resource */
        $tableName = $resource->getTableName('epicor_lists_contract');

        $query = 'UPDATE ' . $tableName . ' SET contract_status = "I" WHERE list_id IN(' . $listIds . ')';

        $writeConnection = $resource->getConnection('core_write');
        /* @var $writeConnection ClassName */
        $writeConnection->query($query);
    }

}
