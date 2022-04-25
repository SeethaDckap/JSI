<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\GroupSave;

use Epicor\OrderApproval\Model\Groups\Erp\AccountFactory as GroupsErpAccountFactory;
use Epicor\OrderApproval\Model\GroupSave\Utilities as SaveUtilites;
use Epicor\OrderApproval\Model\GroupSave\Groups as SaveGroups;
use Epicor\OrderApproval\Model\ErpAccountRepository as ErpAccountRepository;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Erp\Account\CollectionFactory as ErpAccountCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Collection as GroupCollection;

class ErpAccount
{
    /**
     * @var GroupsErpAccountFactory
     */
    private $erpAccountFactory;

    /**
     * @var Utilities
     */
    private $utilities;

    /**
     * @var Groups
     */
    private $saveGroups;

    /**
     * @var ErpAccountRepository
     */
    private $erpAccountRepository;

    /**
     * @var ErpAccountCollectionFactory
     */
    private $erpAccountCollectionFactory;

    /**
     * @var mixed|string
     */
    private $customerErpAccountId;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * ErpAccount constructor.
     * @param GroupsErpAccountFactory $erpAccountFactory
     * @param Utilities $utilities
     * @param Groups $saveGroups
     * @param ErpAccountRepository $erpAccountRepository
     * @param ErpAccountCollectionFactory $erpAccountCollectionFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        GroupsErpAccountFactory $erpAccountFactory,
        SaveUtilites $utilities,
        SaveGroups $saveGroups,
        ErpAccountRepository $erpAccountRepository,
        ErpAccountCollectionFactory $erpAccountCollectionFactory,
        ResourceConnection $resourceConnection
    ) {

        $this->erpAccountFactory = $erpAccountFactory;
        $this->utilities = $utilities;
        $this->saveGroups = $saveGroups;
        $this->erpAccountRepository = $erpAccountRepository;
        $this->erpAccountCollectionFactory = $erpAccountCollectionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveGroupErpAccount()
    {
        if ($this->getCustomerErpAccountId() && $this->saveGroups->getMainGroupId() && !$this->isErpSetForGroup()) {
            /** @var \Epicor\OrderApproval\Model\Groups\Erp\Account $erpAccount */
            $erpAccount = $this->erpAccountFactory->create();
            $erpAccount->setGroupId($this->saveGroups->getMainGroupId());
            $erpAccount->setErpAccountId($this->getCustomerErpAccountId());
            $this->erpAccountRepository->save($erpAccount);
        }
    }

    /**
     * @param GroupCollection $collection
     * @param array $selected
     */
    public function addMasterShopperErpAccountFilter($collection, $selected = [])
    {
        $ids = $this->getMasterShopperApprovalGroups();
        if ($selected && is_array($selected) & is_array($ids)) {
            $ids = array_merge($ids, $selected);
        }
        $collection->addFieldToFilter('main_table.group_id', ['in' => $ids]);
    }

    /**
     * @param $collection
     * @return array|false|string
     */
    public function getMasterShopperApprovalGroups()
    {
        $customer = $this->utilities->getCustomer();
        if (!$erpId = $customer->getData('ecc_erpaccount_id')) {
            return false;
        }
        $customerId = $customer->getEntityId();
        $customerEmail = $customer->getEmail();
        $connection = $this->resourceConnection->getConnection();
        $groupTable = $connection->getTableName('ecc_approval_group');
        $erpGroupTable = $connection->getTableName('ecc_approval_group_erp_account');
        $customerTable = $connection->getTableName('ecc_approval_group_customer');

        $sql = "SELECT DISTINCT eag.group_id FROM $groupTable eag  
                    left join $erpGroupTable eagea ON eagea.group_id = eag.group_id 
                    left join $customerTable eagc on eagc.group_id = eag.group_id
                    where (eagea.erp_account_id = $erpId OR eagea.erp_account_id is null)
                    and (eagc.customer_id is null or eagc.customer_id = $customerId)
                    or (eag.created_by = '$customerEmail')
                    ";

        $result = $connection->fetchAll($sql);

        return $this->getGroupErpAccountId($result);
    }

    /**
     * @param $result
     * @return array|string
     */
    private function getGroupErpAccountId($result)
    {
        $groupIds = [];
        if (!is_array($result)) {
            return $groupIds;
        }
        foreach ($result as $row) {
            $id = $row['group_id'] ?? '';
            if ($id) {
                $groupIds[] = $id;
            }
        }

        return !empty($groupIds) ? $groupIds : '';
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isErpSetForGroup()
    {
        /** @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Erp\Account\Collection $erpAccountCollection */
        $erpAccountCollection = $this->erpAccountCollectionFactory->create();
        $erpAccountCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter('group_id', ['eq' => $this->saveGroups->getMainGroupId()])
            ->addFieldToFilter('erp_account_id', ['eq' => $this->getCustomerErpAccountId()]);

        $result = $erpAccountCollection->getAllIds();

        return !empty($result);
    }

    /**
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerErpAccountId()
    {
        if (!$this->customerErpAccountId) {
            $this->customerErpAccountId = $this->getCustomerAttribute('ecc_erpaccount_id');
        }

        return $this->customerErpAccountId;
    }

    /**
     * @param $attributeCode
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomerAttribute($attributeCode)
    {
        /** @var \Magento\Customer\Model\Data\Customer $data */
        $data = $this->utilities->getCustomerData();
        $eccErpAccountAttribute = $data->getCustomAttribute($attributeCode);

        return $eccErpAccountAttribute->getValue() ?? '';
    }
}