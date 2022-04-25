<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Magento\Backend\Helper\Js as BackendJsHelper;
use Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory as ErpCollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection as ERPCollection;
use Epicor\OrderApproval\Model\CustomerRepository as CustomerRepository;
use Epicor\OrderApproval\Api\Data\CustomerInterfaceFactory as CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Customer\CollectionFactory as customerCollection;

/**
 * Model Class for OrderApproval
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Ecc Team
 *
 */
class CustomerManagement
{
    /**
     * Add Customer
     */
    const ACTION_ADD = 'add';

    /**
     * Remove Customer
     */
    const ACTION_REMOVE = 'remove';

    /**
     * Update Customer
     */
    const ACTION_UPDATE = 'update';

    /**
     * Customer Key
     */
    const KEY_CUSTOMERS = 'customer';

    /**
     * ERP Account
     */
    const KEY_ERP_ACCOUNTS = 'erp_accounts';

    /**
     * B2B Type
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
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var BackendJsHelper
     */
    private $backendJsHelper;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var customerCollection
     */
    private $customersCollection;

    /**
     * CustomerManagement constructor.
     *
     * @param ErpCollectionFactory                                         $erpAccountCollectionFactory
     * @param CustomerCollectionFactory                                    $customerCollectionFactory
     * @param BackendJsHelper                                              $backendJsHelper
     * @param CustomerInterfaceFactory                                     $customerInterfaceFactory
     * @param \Epicor\OrderApproval\Model\CustomerRepository               $customerRepository
     * @param DataObjectHelper                                             $dataObjectHelper
     * @param customerCollection                                           $customersCollection
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array                                                        $data
     */
    public function __construct(
        ErpCollectionFactory $erpAccountCollectionFactory,
        CustomerCollectionFactory $customerCollectionFactory,
        BackendJsHelper $backendJsHelper,
        CustomerInterfaceFactory $customerInterfaceFactory,
        CustomerRepository $customerRepository,
        DataObjectHelper $dataObjectHelper,
        customerCollection $customersCollection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->erpAccountCollectionFactory = $erpAccountCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->backendJsHelper = $backendJsHelper;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customersCollection = $customersCollection;
    }

    /**
     * @param array    $data
     * @param null|int $groupId
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processCustomers($data, $groupId = null)
    {
        $this->groupId = $groupId;
        $customers = isset($data['links']['customers'])
            ? array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['customers']))
            : array();
        $this->removeCustomers($this->getCustomers());
        $this->addCustomers($customers);
        $this->removeOrphanCustomers();
        $this->saveCustomers();
    }

    /**
     * @param false|int $groupId
     *
     * @return array
     */
    public function getErpAccounts($groupId = false)
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
     * @param array|int|object $customers
     */
    public function addCustomers($customers)
    {
        $this->changes($customers, self::KEY_CUSTOMERS, self::ACTION_ADD);
    }

    /**
     * Removes customers from the Groups
     *
     * @param array|int|object $customers
     */
    public function removeCustomers($customers)
    {
        $this->changes($customers, self::KEY_CUSTOMERS, self::ACTION_REMOVE);
    }

    /**
     * @param array|int|object $items
     * @param string           $section
     * @param string           $action
     * @param false            $idField
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
     * @param bool $byGroup
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function saveCustomers($byGroup = true)
    {
        if (isset($this->changes[self::KEY_CUSTOMERS])) {
            $existingCustomers = $this->getCustomers(false, false);

            if (isset($this->changes[self::KEY_CUSTOMERS][self::ACTION_ADD])
                && is_array($this->changes[self::KEY_CUSTOMERS][self::ACTION_ADD])
            ) {
                foreach (
                    $this->changes[self::KEY_CUSTOMERS][self::ACTION_ADD] as
                    $customerId => $customer
                ) {
                    if ( ! array_key_exists($customerId, $existingCustomers)) {
                        /** @var \Epicor\OrderApproval\Model\Groups\Customer $customer */
                        $customer = $this->customerInterfaceFactory->create();
                        $this->dataObjectHelper->populateWithArray($customer,
                            [
                                \Epicor\OrderApproval\Api\Data\CustomerInterface::GROUP_ID    => $this->getGroupId(),
                                \Epicor\OrderApproval\Api\Data\CustomerInterface::CUSTOMER_ID => $customerId,
                                \Epicor\OrderApproval\Api\Data\CustomerInterface::BY_GROUP    => 1,
                                \Epicor\OrderApproval\Api\Data\CustomerInterface::BY_CUSTOMER => 0,
                            ],
                            Epicor\OrderApproval\Api\Data\GroupsInterface::class);

                        //$customer->save();
                        $this->customerRepository->save($customer);
                    } elseif (array_key_exists($customerId, $existingCustomers)
                        && isset($existingCustomers[$customerId])
                    ) {
                        $existRow = $existingCustomers[$customerId];
                        /* @var \Epicor\Dealerconnect\Model\Customer\Erpaccount $existRow */

                        if ($existRow->getData("by_customer") == "1"
                            && $existRow->getData("group_id")
                        ) {
                            /** @var \Epicor\OrderApproval\Model\Groups\Customer $customer */
                            $customer
                                = $this->customerRepository->getById($existRow->getData("id"));

                            $this->dataObjectHelper->populateWithArray($customer,
                                [
                                    \Epicor\OrderApproval\Api\Data\CustomerInterface::BY_GROUP => 1,
                                ],
                                Epicor\OrderApproval\Api\Data\GroupsInterface::class);

                            //$customer->save();
                            $this->customerRepository->save($customer);
                        }
                    }
                }

                unset($this->changes[self::KEY_CUSTOMERS][self::ACTION_ADD]);
            }

            if (isset($this->changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE])
                && is_array($this->changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE])
            ) {
                $customerIds = array();

                foreach (
                    $this->changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE] as
                    $customerId => $customer
                ) {
                    if (array_key_exists($customerId, $existingCustomers)) {
                        $customerIds[] = $customerId;
                    }
                }

                if (count($customerIds) > 0) {
                    /* @var $customersCollection \Epicor\OrderApproval\Model\ResourceModel\Groups\Customer\Collection */
                    $customersCollection
                        = $this->customersCollection->create();

                    $customersCollection->addFieldtoFilter(
                        'group_id',
                        $this->getGroupId()
                    );

                    $customersCollection->addFieldtoFilter(
                        'customer_id',
                        array('in' => $customerIds)
                    );

                    //$customersCollection->addFieldtoFilter('by_group', "1");

                    foreach ($customersCollection->getItems() as $item) {
                        $this->customerRepository->delete($item);
                    }
                }

                unset($this->changes[self::KEY_CUSTOMERS][self::ACTION_REMOVE]);
            }
        }
    }

    /**
     * @param false|string $groupId
     * @param bool         $byGroup
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomers($groupId = false, $byGroup = true)
    {
        if ($groupId) {
            $this->groupId = $groupId;
        }

        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
        $collection = $this->customerCollectionFactory->create();
        $collection->addAttributeToSelect('ecc_erp_account_type');
        $collection->addAttributeToSelect('ecc_erpaccount_id');
        if ($byGroup) {
            $collection->getSelect()->join(
                array('customer' => $collection->getTable('ecc_approval_group_customer')),
                'e.entity_id = customer.customer_id AND customer.group_id = "'
                .$this->getGroupId().'"', array('*')
            );
        } else {
            $collection->getSelect()->join(
                array('customer' => $collection->getTable('ecc_approval_group_customer')),
                'e.entity_id = customer.customer_id AND customer.group_id = "'
                .$this->getGroupId().'"', array('*')
            );
        }

        $items = array();
        foreach ($collection->getItems() as $item) {
            $items[$item->getId()] = $item;
        }

        return $items;
    }

    /**
     * Removes orphan customers
     *
     * @param boolean $warn
     *
     * @return boolean|array
     */
    private function removeOrphanCustomers($warn = false)
    {
        $customers = $this->getCustomersWithChanges();

        if (empty($customers)) {
            return false;
        }

        $removeType = $this->getAccountTypeToRemove();
        $removeCustomers = array();
        foreach ($customers as $key => $customer) {
            /* @var $customer \Epicor\Dealerconnect\Model\Customer */
            if ($this->shouldRemoveCustomer($customer, $removeType)) {
                $removeCustomers[$key] = $customer;
            }
        }

        if (empty($removeCustomers)) {
            return false;
        }

        if ($warn == false) {
            $this->removeCustomers($removeCustomers);
        }

        return $removeCustomers;
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
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomersWithChanges()
    {
        $customers = $this->getCustomers();

        if (isset($this->changes[self::KEY_CUSTOMERS])) {
            foreach ($this->changes[self::KEY_CUSTOMERS] as $type => $items) {
                if ($type == self::ACTION_ADD) {
                    /* @var $collection \Magento\Customer\Model\ResourceModel\Customer\Collection */
                    $collection = $this->customerCollectionFactory->create();
                    $collection->addAttributeToSelect('ecc_erp_account_type',
                        'left');
                    $collection->addAttributeToSelect('ecc_erpaccount_id',
                        'left');
                    $collection->addFieldToFilter('entity_id',
                        array_keys($items));
                    $customerCollection = $collection->getItems();

                    $customers = $customers + $customerCollection;
                } else {
                    if ($type == self::ACTION_REMOVE) {
                        foreach ($items as $key => $item) {
                            if (isset($customers[$key])) {
                                unset($customers[$key]);
                            }
                        }
                    }
                }
            }
        }

        return $customers;
    }

    /**
     * @param \Epicor\Dealerconnect\Model\Customer $customer
     * @param array                                $removeType
     *
     * @return bool
     */
    private function shouldRemoveCustomer($customer, $removeType)
    {
        // sales reps should never be removed
        if ($customer->isSalesRep()) {
            return false;
        }

        $customerEdpAccountType = [];
        if ($customer->getCustomerErpAccount()) {
            $customerEdpAccountType = $customer->getCustomerErpAccount()
                ->getAccountType() ?: [];
        }

        // simple check: does the customer match the correct link type
        if ((count(array_intersect([$customerEdpAccountType], $removeType))
                && $customer->isCustomer(false))
            || (in_array('B2C', $removeType) && $customer->isGuest(false))
        ) {
            return true;
        }

        $erpAccounts = $this->getErpAccountsWithChanges();

        $accountSelected = isset($erpAccounts[$customer->getEccErpaccountId()]);
        if ($accountSelected === false && count($erpAccounts) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    protected function getGroupId()
    {
        return $this->groupId;
    }
}
