<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Epicor\OrderApproval\Api\ErpAccountBudgetRepositoryInterface;
use Epicor\OrderApproval\Api\Data;
use Epicor\OrderApproval\Api\Data\ErpAccountBudgetInterface;
use Epicor\OrderApproval\Model\ErpAccountBudgetFactory as ErpAccountBudgetFactory;
use Epicor\OrderApproval\Model\ResourceModel\ErpAccountBudget;
use Epicor\OrderApproval\Model\ResourceModel\ErpAccountBudgetFactory as BudgetResourceFactory;
use Epicor\OrderApproval\Model\ResourceModel\ErpAccountBudget\CollectionFactory as CollectionFactory;
use Epicor\OrderApproval\Model\ResourceModel\ErpAccountBudget\Collection;

class ErpAccountBudgetRepository implements ErpAccountBudgetRepositoryInterface
{
    /**
     * @var ErpAccountBudget
     */
    private $resource;

    /**
     * @var ErpAccountBudgetFactory
     */
    private $erpAccountBudgetFactory;

    /**
     * @var BudgetResourceFactory
     */
    private $budgetResourceFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * ErpAccountBudgetRepository constructor.
     *
     * @param ErpAccountBudget        $resource
     * @param ErpAccountBudgetFactory $erpAccountBudgetFactory
     * @param BudgetResourceFactory   $budgetResourceFactory
     * @param CollectionFactory       $collectionFactory
     */
    public function __construct(
        ErpAccountBudget $resource,
        ErpAccountBudgetFactory $erpAccountBudgetFactory,
        BudgetResourceFactory $budgetResourceFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->erpAccountBudgetFactory = $erpAccountBudgetFactory;
        $this->budgetResourceFactory = $budgetResourceFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param ErpAccountBudgetInterface|\Epicor\OrderApproval\Model\ErpAccountBudget $erpAccountBudget
     * @return Data\ErpAccountBudgetInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save(Data\ErpAccountBudgetInterface $erpAccountBudget)
    {
        try {
            $this->resource->save($erpAccountBudget);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the ERP account budget: %1', $exception->getMessage()),
                $exception
            );
        }

        return $erpAccountBudget;
    }

    /**
     * @param int $erpBudgetId
     *
     * @return ErpAccountBudgetInterface|\Epicor\OrderApproval\Model\ErpAccountBudget
     * @throws NoSuchEntityException
     */
    public function getById($erpBudgetId)
    {
        /** @var \Epicor\OrderApproval\Model\ErpAccountBudget $erpAccountBudget */
        $erpAccountBudget = $this->erpAccountBudgetFactory->create();
        if ($erpBudgetId) {
            $this->budgetResourceFactory->create()->load($erpAccountBudget, $erpBudgetId);
        }

        if (!$erpAccountBudget->getId()) {
            throw new NoSuchEntityException(
                __('The group with the "%1" groupId doesn\'t exist.', $erpBudgetId)
            );
        }

        return $erpAccountBudget;
    }

    /**
     * @param ErpAccountBudgetInterface|\Epicor\OrderApproval\Model\ErpAccountBudget $erpAccountBudget
     * @return mixed
     * @throws CouldNotSaveException
     */
    public function delete(ErpAccountBudgetInterface $erpAccountBudget)
    {
        try {
            $this->resource->delete($erpAccountBudget);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not delete the ERP account budget: %1', $exception->getMessage()),
                $exception
            );
        }

        return true;
    }

    /**
     * @param string $ErpId
     *
     * @return array|\Magento\Framework\DataObject[]
     */
    public function getByErpId($ErpId)
    {
        $items = [];
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('erp_id', $ErpId);
        if ($collection->count() > 0) {
            $items = $collection->getItems();
        }

        return $items;
    }
}
