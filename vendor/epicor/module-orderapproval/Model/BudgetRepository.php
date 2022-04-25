<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Epicor\OrderApproval\Api\BudgetRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Budget as Resource;
use Epicor\OrderApproval\Api\Data\BudgetInterface;
use Epicor\OrderApproval\Api\Data\BudgetInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Budget\CollectionFactory as CollectionFactory;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Budget\Collection;

/**
 * Class BudgetRepository
 */
class BudgetRepository implements BudgetRepositoryInterface
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $customerIds = [];

    /**
     * @var BudgetInterfaceFactory
     */
    private $budgetInterfaceFactory;

    /**
     * BudgetRepository constructor.
     * @param BudgetInterfaceFactory $budgetInterfaceFactory
     * @param Resource $resource
     */
    public function __construct(
        BudgetInterfaceFactory $budgetInterfaceFactory,
        Resource $resource,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->budgetInterfaceFactory = $budgetInterfaceFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param BudgetInterface $budget
     *
     * @return BudgetInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save(BudgetInterface $budget)
    {
        try {
            $this->resource->save($budget);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save the budget: %1',
                    $exception->getMessage()
                ),
                $exception
            );
        }

        return $budget;
    }

    /**
     * @param string $groupId
     * @param array $excludeBudgetIds
     *
     * @return int
     * @throws CouldNotDeleteException
     */
    public function deleteByGroupId($groupId, $excludeBudgetIds = [])
    {
        try {
            $where['group_id = ?'] = $groupId;
            if (count($excludeBudgetIds) > 0) {
                $where['id NOT IN (?) '] = $excludeBudgetIds;
            }
            $count = $this->resource->getConnection()
                ->delete(
                    'ecc_approval_group_budget',
                    $where
                );
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Budget: %1', $exception->getMessage())
            );
        }

        return $count;
    }

    /**
     * @param BudgetInterface $budget
     *
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function delete(BudgetInterface $budget)
    {
        try {
            $this->resource->delete($budget);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Budget: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * @param string $budgetId
     * @return Groups\Budget
     * @throws NoSuchEntityException
     */
    public function getById($budgetId)
    {
        /** @var \Epicor\OrderApproval\Model\Groups\Budget $budget */
        $budget = $this->budgetInterfaceFactory->create();
        if ($budgetId) {
            $this->resource->load($budget, $budgetId);
        }

        if (!$budget->getId()) {
            throw new NoSuchEntityException(
                __('The budget with the id "%1"  doesn\'t exist.', $budgetId)
            );
        }

        return $budget;
    }

    /**
     * @param $groupId
     *
     * @return array|\Magento\Framework\DataObject[]
     */
    public function getByGroupId($groupId)
    {
        $items = [];
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('group_id', $groupId);
        if ($collection->count() > 0) {
            $items = $collection->getItems();
        }

        return $items;
    }

    /**
     * get Order Total By CustomerId.
     *
     * @param array $customerIds
     * @param array $dates
     * @param bool  $excludeERP
     * @param array $excludeOrderId
     *
     * @return string
     */
    public function getOrderTotalByCustomerId($customerIds, $dates, $excludeERP, $excludeOrderId)
    {
        $strCusId = implode(',', $customerIds);
        $fromDate = $dates['start_date']." 00:00:00";
        $endDate = $dates['end_date']." 00:00:00";

        if ($fromDate == $endDate) {
            $endDate = $dates['end_date']." 23:59:00";
        }

        return $this->resource->getOrderTotal($strCusId, $fromDate, $endDate, $excludeERP, $excludeOrderId);
    }

    /**
     * @param string $erpAccountId
     *
     * @return array
     */
    public function getCustomerIdsByErpId($erpAccountId)
    {
        if (!$this->customerIds) {
            $this->customerIds = $this->resource->getCustomerIdsbyErpId(
                $erpAccountId
            );
        }

        return $this->customerIds;
    }


}
