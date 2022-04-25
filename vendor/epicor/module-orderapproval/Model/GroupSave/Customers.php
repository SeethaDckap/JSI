<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\GroupSave;

use Epicor\OrderApproval\Model\Groups\Customer;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Customer\CollectionFactory as CustomerCollectionFactory;
use Epicor\OrderApproval\Model\GroupSave\Utilities as SaveUtilites;
use Epicor\OrderApproval\Api\CustomerRepositoryInterface as CustomerRepository;
use Epicor\OrderApproval\Model\Groups\CustomerFactory as CustomerFactory;
use Epicor\OrderApproval\Model\GroupSave\Groups as SaveGroups;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Customer\Collection as GroupsCustomerCollection;
use Epicor\OrderApproval\Model\GroupsRepository;
use Epicor\OrderApproval\Model\Groups;
use Magento\Framework\App\RequestInterface;
use Epicor\AccessRight\Model\RoleModel\Erp\Account as ErpAccount;
use Epicor\AccessRight\Model\Authorization;

class Customers
{
    const FRONTEND_RESOURCE_GROUP_EDIT = 'Epicor_Customer::my_account_group_edit';

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var Utilities
     */
    private $utilities;

    /**
     * @var array|mixed
     */
    private $newCustomerIds;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var array
     */
    private $currentCustomerIds;

    /**
     * @var Groups
     */
    private $saveGroups;

    /**
     * @var GroupsRepository
     */
    private $groupsRepository;

    /**
     * @var Groups
     */
    private $groups;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ErpAccount
     */
    private $erpAccount;

    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * Customers constructor.
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Utilities $utilities
     * @param CustomerRepository $customerRepository
     * @param CustomerFactory $customerFactory
     * @param \Epicor\OrderApproval\Model\GroupSave\Groups $saveGroups
     * @param GroupsRepository $groupsRepository
     * @param Groups $groups
     * @param RequestInterface $request
     * @param ErpAccount $erpAccount
     * @param Authorization $authorization
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory,
        SaveUtilites $utilities,
        CustomerRepository $customerRepository,
        CustomerFactory $customerFactory,
        SaveGroups $saveGroups,
        GroupsRepository $groupsRepository,
        Groups $groups,
        RequestInterface $request,
        ErpAccount $erpAccount,
        Authorization $authorization
    ) {

        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->utilities = $utilities;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->saveGroups = $saveGroups;
        $this->groupsRepository = $groupsRepository;
        $this->groups = $groups;
        $this->request = $request;
        $this->erpAccount = $erpAccount;
        $this->authorization = $authorization;
    }

    /**
     * @return void
     */
    public function saveCustomers()
    {
        $this->newCustomerIds = $this->getSubmittedCustomerIds();
        if (!empty($this->newCustomerIds) || $this->isSelectedCustomer()) {
            $this->removeCustomersFromGroup();
            $this->insertNewCustomersIntoGroup();
        }
    }

    /**
     * @return array
     */
    private function getSubmittedCustomerIds()
    {
        $ids = [];
        $postData = $this->utilities->getPostData();
        $postCustomerIds = $postData['customers'] ?? [];
        foreach ($postCustomerIds as $key => $id) {
            if (is_numeric($id)) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isMasterShopper()
    {
        return (boolean) $this->getCustomerAttribute('ecc_master_shopper');
    }


    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isMasterShopperB2B()
    {
        return $this->isMasterShopper() && $this->isTypeB2B();
    }

    /**
     * @return bool
     */
    public function isTypeB2B()
    {
        $customer = $this->utilities->getCustomer();
        if (!$customer instanceof \Magento\Customer\Model\Customer) {
            return false;
        }
        if (!$erpAccountId = $customer->getData('ecc_erpaccount_id')) {
            return false;
        }

        return $this->erpAccount->getErpAccountType($erpAccountId) === 'B';
    }

    /**
     * @param $code
     * @return bool
     */
    private function isAccessAllowed($code)
    {
        return $this->authorization->isAllowed($code);
    }

    /**
     * @param string $groupIdentifier
     * @param null $groupId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isEditableByCustomer($groupIdentifier = 'id', $groupId = null)
    {
        if (!$this->isAccessAllowed(self::FRONTEND_RESOURCE_GROUP_EDIT) && $this->request->getParam('id')) {
            return false;
        }
        if ($groupId === null) {
            $groupId = $this->getGroupId($groupIdentifier);
        }

        if (!$groupId) {
            return true;
        }
        $group = $this->getGroup($groupId);
        if (!$group || !$group instanceof \Epicor\OrderApproval\Model\Groups) {
            return false;
        }

        $createdBy = $group->getData('created_by');
        $customerEmail = $this->utilities->getCustomer()->getEmail();

        return $createdBy === $customerEmail;
    }

    /**
     * @param $groupId
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getGroup($groupId)
    {
        if (!$groupId) {
            return $this->groups;
        }

        return $this->groupsRepository->getById($groupId);
    }

    /**
     * @param $groupIdentifier
     * @return mixed|null
     */
    public function getGroupId($groupIdentifier)
    {
        if ($id = $this->request->getParam($groupIdentifier)) {
            return $id;
        }
    }

    /**
     * @param $attributeCode
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerAttribute($attributeCode)
    {
        /** @var \Magento\Customer\Model\Data\Customer $data */
        $data = $this->utilities->getCustomerData();
        $eccErpAccountAttribute = $data->getCustomAttribute($attributeCode);

        return $eccErpAccountAttribute->getValue() ?? '';
    }

    /**
     * @return bool
     */
    private function isSelectedCustomer()
    {
        $data = $this->utilities->getPostData();
        return (boolean) $data['selected_customers']?? false;
    }

    /**
     * @return void
     */
    private function removeCustomersFromGroup()
    {
        $currentCustomerIds = $this->getCurrentCustomerIds();
        /** @var Customer $customer */
        foreach ($currentCustomerIds as $id => $customer) {
            if (!in_array($id, $this->newCustomerIds)) {
                $this->customerRepository->delete($customer);
            }
        }
    }

    /**
     * @return array
     */
    private function getCurrentCustomerIds()
    {
        if (!$this->currentCustomerIds) {
            $this->currentCustomerIds = [];
            foreach ($this->getCurrentCustomerCollection() as $customer) {
                $id = $customer->getData('customer_id');
                $this->currentCustomerIds[$id] = $customer;
            }
        }

        return $this->currentCustomerIds;
    }

    /**
     * @return GroupsCustomerCollection
     */
    private function getCurrentCustomerCollection()
    {
        $collection = $this->customerCollectionFactory->create();

        return $collection->addFieldToFilter('group_id', ['eq' => $this->saveGroups->getMainGroupId()]);
    }

    /**
     * @return void
     */
    private function insertNewCustomersIntoGroup()
    {
        foreach ($this->newCustomerIds as $id) {
            if (!key_exists($id, $this->getCurrentCustomerIds())) {
                $customer = $this->customerFactory->create();
                $customer->setData('customer_id', $id);
                $customer->setData('group_id', $this->saveGroups->getMainGroupId());
                $customer->setData('by_customer', 1);
                $this->customerRepository->save($customer);
            }
        }
    }
}
