<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Helper;

use Epicor\Comm\Model\Customer\Erpaccount as Erpaccount;
class Admin extends \Epicor\Comm\Helper\Data
{
    /**
     * @var array
     */
//    private $_erplinkTypes = array(
//        'B' => 'B2B',
//        'C' => 'B2C',
//        'R' => 'Dealer',
//        'D' => 'Distributor',
//        'S' => 'Supplier',
//    );

    /**
     * @var \Epicor\AccessRight\Model\ResourceModel\RoleModel\CollectionFactory
     */
    protected $rolesResourceRoleModelCollectionFactory;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    public function __construct(
        \Epicor\Comm\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Epicor\AccessRight\Model\ResourceModel\RoleModel\CollectionFactory  $rolesResourceRoleModelCollectionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->rolesResourceRoleModelCollectionFactory = $rolesResourceRoleModelCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Checks whether roles can be assigned to given ERP accounts
     * 
     * @param array $roleIds
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * 
     * @return array
     */
    public function assignErpAccountRolesCheck($roleIds, $erpAccount)
    {
        $newRoles = $rolesCollection = $this->rolesResourceRoleModelCollectionFactory->create();
        $newRoles->addFieldtoFilter('id', array('in' => $roleIds));

        $return = array();

        $accountType = $erpAccount->getAccountType();
        $changeLinkTypes = array();

        foreach ($newRoles as $newRole) {
            $linkType = $newRole->getErpAccountLinkType();
            if (
                (in_array($linkType, array('E', 'N'))) ||
                (($accountType == Erpaccount::CUSTOMER_TYPE_B2C) && ($linkType == 'C')) ||
                (($accountType == Erpaccount::CUSTOMER_TYPE_B2B) && ($linkType == 'B')) ||
                (($accountType == Erpaccount::CUSTOMER_TYPE_Dealer) && ($linkType == 'R')) ||
                (($accountType == Erpaccount::CUSTOMER_TYPE_Distributor) && ($linkType == 'D')) ||
                (($accountType == Erpaccount::CUSTOMER_TYPE_SUPPLIER ) && ($linkType == 'S'))
            ) {
                $return['success']['values'][] = $newRole->getId();
                if ($linkType == 'N') {
                    $changeLinkTypes[] = $newRole->getId();
                }
            } else {
                $return['error']['values'][] = $newRole->getId();
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
     * Checks whether roles can be assigned to correct customers ERP accounts
     * 
     * @param array $roleIds
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * 
     * @return array
     */
    public function assignCustomerAccountRolesCheck($roleIds, $customer)
    {
        $newRoles = $rolesCollection = $this->rolesResourceRoleModelCollectionFactory->create();
        /* @var $rolesCollection Epicor\AccessRight\Model\ResourceModel\RoleModel\Collection */
        $newRoles->addFieldtoFilter('id', array('in' => $roleIds));

        $customerAccountType = $customer->getEccErpAccountType();

        $return = array();

        $changeLinkTypes = array();

        $notAllowedTypes = array('guest', 'salesrep');

        $erpAccountType = false;
        $erpaccountId = null;

        if (!in_array($customerAccountType, $notAllowedTypes)) {
            $account = $this->commCustomerErpaccountFactory->create()->load($customer->getEccErpaccountId());
            /* @var $account Epicor\Comm\Model\Customer\Erpaccount */
            $erpAccountType = $account->getAccountType();
            if($customerAccountType == 'supplier'){
                $erpaccountId   = $customer->getEccSupplierErpaccountId();
            }else{
                $erpaccountId = $customer->getEccErpaccountId();
            }
        }

        $return = array();
        foreach ($newRoles as $newRole) {
            /* @var $newRole Epicor\Roles\Model\RoleModel */
            $linkType = $newRole->getErpAccountLinkType();
            $erpIds = $this->erpAccountIds($newRole->getId());
            $isValid = true;

            // NO ERP Accounts Selected
            if (empty($erpIds)) {
                // NO ERP Accounts Selected, then ERP Account type Must Match
                if (in_array($linkType, array('C', 'B'))) {
                    $expectedType = $linkType == 'B' ? 'B2B' : 'B2C';
                    //$expectedType = $this->_erplinkTypes[$linkType];
                    $isValid = ($erpAccountType == $expectedType);
                }
            } else {
                // ERP Account(s) selected, so Customer ERP Account ID MUST be selected
                // ECC should NOT assign the customer to that role because
                // the customer belongs to ERP Account '777' and you attempted to
                // assign to a role who is associated with '999' and '888'
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
                        //$expectedType = $this->_erplinkTypes[$linkType];
                        $isValid = ($erpAccountType == $expectedType);
                    }
                }
            }

            if ($isValid) {
                $return['success']['values'][] = $newRole->getId();
            } else {
                $return['error']['values'][] = $newRole->getId();
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
     * Get all the erpId's for a particular role Id
     */
    public function erpAccountIds($roleId)
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection Epicor\Comm\Model\ResourceCustomer\Erpaccount */
        $collection->addFieldtoSelect('entity_id');
        $collection->getSelect()->join(
            array('erp_account' => $collection->getTable('ecc_access_role_erp_account')), 'main_table.entity_id = erp_account.erp_account_id AND erp_account.access_role_id = "' . $roleId . '"', array()
        );

        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item->getId();
        }

        return $items;
    }

    /**
     * Updates given roles to the given link type
     * 
     * @param array $roleIds
     * @param string $newType
     */
    private function massUpdateLinkTypes($roleIds, $newType)
    {
        $ids = implode(',', $roleIds);
        $resource = $this->resourceConnection;

        $tableName = $resource->getTableName('ecc_access_role');

        $query = 'UPDATE ' . $tableName . ' SET erp_account_link_type = "' . $newType . '" WHERE id IN(' . $ids . ')';

        $writeConnection = $resource->getConnection('core_write');
        $writeConnection->query($query);
    }

}
