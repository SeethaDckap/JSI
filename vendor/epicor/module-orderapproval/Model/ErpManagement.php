<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Magento\Backend\Helper\Js as BackendJsHelper;
use Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory as ErpCollectionFactory;
use Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection as ERPCollection;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Erp\Account\CollectionFactory as ErpGroupsCollectionFactory;
use Epicor\OrderApproval\Model\ErpAccountRepository as ErpRepository;
use Epicor\OrderApproval\Api\Data\ErpAccountInterfaceFactory as ErpAccountInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Model Class for OrderApproval
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Ecc Team
 *
 */
class ErpManagement
{
    /**
     * Add Erp to group.
     */
    const ACTION_ADD = 'add';

    /**
     * Remove Erp to group.
     */
    const ACTION_REMOVE = 'remove';

    /**
     * Update Erp to group.
     */
    const ACTION_UPDATE = 'update';

    /**
     * Erp Accounts.
     */
    const KEY_ERP_ACCOUNTS = 'erp_accounts';

    /**
     * Add Erp to group.
     */
    const ERP_ACC_LINK_TYPE_B2B = 'B';

    /**
     * @var array
     */
    private $changes = array();

    /**
     * @var int
     */
    private $groupId = 0;

    /**
     * @var ErpCollectionFactory
     */
    private $erpAccountCollectionFactory;

    /**
     * @var BackendJsHelper
     */
    private $backendJsHelper;

    /**
     * @var ErpGroupsCollectionFactory
     */
    private $erpResourceCollection;

    /**
     * @var ErpAccountRepository
     */
    private $erpRepository;

    /**
     * @var ErpAccountInterfaceFactory
     */
    private $erpInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    public function __construct(
        ErpCollectionFactory $erpAccountCollectionFactory,
        BackendJsHelper $backendJsHelper,
        ErpGroupsCollectionFactory $erpResourceCollection,
        ErpRepository $erpRepository,
        ErpAccountInterfaceFactory $erpInterfaceFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->erpAccountCollectionFactory = $erpAccountCollectionFactory;
        $this->backendJsHelper = $backendJsHelper;
        $this->erpResourceCollection = $erpResourceCollection;
        $this->erpInterfaceFactory = $erpInterfaceFactory;
        $this->erpRepository = $erpRepository;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param array $data
     * @param null|string  $groupId
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function processERPAccounts($data, $groupId = null)
    {
        $this->groupId = $groupId;

        $erpAccounts
            = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['erpaccounts']));
        $this->removeErpAccounts($this->getErpAccounts());
        $this->addErpAccounts($erpAccounts);
        $this->removeOrphanErpAccounts();
        $this->saveErpAccounts();
    }

    /**
     * @param null|string $groupId
     *
     * @return array
     */
    public function getErpAccounts($groupId = null)
    {
        if ($groupId) {
            $this->groupId = $groupId;
        }

        /* @var $collection ERPCollection */
        $collection = $this->erpAccountCollectionFactory->create();
        $collection->getSelect()->join(
            array(
                'erp_account' => $collection->getTable(
                    'ecc_approval_group_erp_account'
                ),
            ),
            'main_table.entity_id = erp_account.erp_account_id AND erp_account.group_id = "'
            .$this->getGroupId().'"', array('*')
        );

        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item;
        }

        return $items;
    }

    /**
     * Adds erp accounts to the Groups
     *
     * @param array|int|object $erpAccounts
     */
    public function addErpAccounts($erpAccounts)
    {
        $this->changes($erpAccounts, self::KEY_ERP_ACCOUNTS, self::ACTION_ADD);
    }

    /**
     * Removes erp accounts from the Groups
     *
     * @param array|int|object $erpAccounts
     */
    public function removeErpAccounts($erpAccounts)
    {
        $this->changes($erpAccounts, self::KEY_ERP_ACCOUNTS,
            self::ACTION_REMOVE);
    }


    /**
     * Save Data for ERP account Tab.
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function saveErpAccounts()
    {
        if (isset($this->changes[self::KEY_ERP_ACCOUNTS])) {
            $existingErpAccounts = $this->getErpAccounts(false);

            //Add/Map ERP Accounts
            if (isset($this->changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD])
                && is_array($this->changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD])
            ) {
                foreach (
                    $this->changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD] as
                    $erpAccountId => $erpAccount
                ) {
                    if ( ! array_key_exists($erpAccountId,
                        $existingErpAccounts)
                    ) {
                        /** @var \Epicor\OrderApproval\Model\Groups\Erp\Account $erpAccount */
                        $erpAccount = $this->erpInterfaceFactory->create();
                        $this->dataObjectHelper->populateWithArray($erpAccount,
                            [
                                \Epicor\OrderApproval\Api\Data\ErpAccountInterface::GROUP_ID       => $this->getGroupId(),
                                \Epicor\OrderApproval\Api\Data\ErpAccountInterface::ERP_ACCOUNT_ID => $erpAccountId,
                            ],
                            Epicor\OrderApproval\Api\Data\GroupsInterface::class);
                        $this->erpRepository->save($erpAccount);
                    }
                    $getAllIds[] = $erpAccountId;
                }

                unset($this->changes[self::KEY_ERP_ACCOUNTS][self::ACTION_ADD]);
            }

            //Delete ERP Accounts
            if (isset($this->changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE])
                && is_array($this->changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE])
            ) {
                $erpAccountIds = array();
                foreach (
                    $this->changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE]
                    as $erpAccountId => $erpAccount
                ) {
                    if (array_key_exists($erpAccountId, $existingErpAccounts)) {
                        $erpAccountIds[] = $erpAccountId;
                    }
                }

                if (count($erpAccountIds) > 0) {
                    $erpCollection = $this->erpResourceCollection->create();
                    /* @var $erpCollection \Epicor\OrderApproval\Model\ResourceModel\Groups\Erp\Account\Collection */
                    $erpCollection->addFieldtoFilter(
                        'group_id',
                        $this->getGroupId()
                    );
                    $erpCollection->addFieldtoFilter(
                        'erp_account_id',
                        array('in' => $erpAccountIds)
                    );

                    foreach ($erpCollection->getItems() as $item) {
                        $this->erpRepository->delete($item);
                    }
                }

                unset($this->changes[self::KEY_ERP_ACCOUNTS][self::ACTION_REMOVE]);
            }
        }
    }

    /**
     * Looks at the ERP Accounts to determine if they need to be
     *
     * @param boolean $warn
     *
     * @return boolean|array
     */
    private function removeOrphanErpAccounts($warn = false)
    {
        $erpAccounts = $this->getErpAccountsWithChanges();
        if (empty($erpAccounts)) {
            return false;
        }

        $removeType = $this->getAccountTypeToRemove();
        if (in_array('None', $removeType)) {
            return false;
        }

        $removeErpAccounts = array();
        foreach ($erpAccounts as $key => $erpAccount) {
            /* @var $erpAccount \Epicor\Dealerconnect\Model\Customer\Erpaccount */
            if (in_array($erpAccount->getAccountType(), $removeType)
                || in_array('All', $removeType)
            ) {
                $removeErpAccounts[$key] = $erpAccount;
            }
        }

        if (empty($removeErpAccounts)) {
            return false;
        }

        if ($warn == false) {
            $this->removeErpAccounts($removeErpAccounts);
        }

        return $removeErpAccounts;
    }

    /**
     * @return array
     */
    private function getAccountTypeToRemove()
    {
        return ['B2C', 'Dealer', 'Distributor', 'Supplier'];
    }

    /**
     * @return array|\Magento\Framework\DataObject[]
     */
    public function getErpAccountsWithChanges()
    {
        $erpAccounts = $this->getErpAccounts();
        if (isset($this->changes[self::KEY_ERP_ACCOUNTS])) {
            foreach ($this->changes[self::KEY_ERP_ACCOUNTS] as $type => $items)
            {
                if ($type == self::ACTION_ADD) {
                    /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection */
                    $collection = $this->erpAccountCollectionFactory->create();
                    $collection->addFieldToFilter('entity_id',
                        array_keys($items));
                    $collectionItems = $collection->getItems();
                    $erpAccounts = $erpAccounts + $collectionItems;
                } else {
                    if ($type == self::ACTION_REMOVE) {
                        foreach ($items as $key => $item) {
                            if (isset($erpAccounts[$key])) {
                                unset($erpAccounts[$key]);
                            }
                        }
                    }
                }
            }
        }

        return $erpAccounts;
    }

    /**
     * @param array|int|object $items
     * @param string           $section
     * @param string           $action
     * @param false|string            $idField
     */
    private function changes($items, $section, $action, $idField = false)
    {
        $verify = ($action == self::ACTION_ADD)
            ? self::ACTION_REMOVE
            : self::ACTION_ADD;

        if (is_array($items) === false) {
            $items = array($items);
        }

        foreach ($items as $item) {
            $itemId = (is_object($item)
                ? ($idField ? $item->getData($idField) : $item->getId())
                : $item);
            $this->changes[$section][$action][$itemId] = $item;
            if (isset($this->changes[$section][$verify][$itemId])) {
                unset($this->changes[$section][$verify][$itemId]);
            }
        }
    }

    /**
     * @return int
     */
    private function getGroupId()
    {
        return $this->groupId;
    }
}
