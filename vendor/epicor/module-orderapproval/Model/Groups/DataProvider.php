<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Groups;

use Epicor\OrderApproval\Model\ResourceModel\Groups\Collection;
use Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory;

use Epicor\OrderApproval\Model\ResourceModel\Groups\Budget\Collection as BudgetCollection;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Budget\CollectionFactory as BudgetCollectionFactory;
use Magento\CatalogRule\Model\Groups;
use Magento\Framework\App\Request\DataPersistorInterface;
use Epicor\OrderApproval\Ui\Component\Listing\Column\BudgetTypes;

/**
 * Class DataProvider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var BudgetCollection
     */
    protected $budgetCollection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param string                  $name
     * @param string                  $primaryFieldName
     * @param string                  $requestFieldName
     * @param CollectionFactory       $collectionFactory
     * @param BudgetCollectionFactory $budgetCollectionFactory
     * @param DataPersistorInterface  $dataPersistor
     * @param array                   $meta
     * @param array                   $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        BudgetCollectionFactory $budgetCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->budgetCollection = $budgetCollectionFactory->create();
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Groups $Group */
        foreach ($items as $Group) {
            $groupData = $Group->getData();
            $this->loadedData[$Group->getId()]["group"] = $groupData;
            $this->loadedData[$Group->getId()]["budget"]["is_budget_active"]
                = $groupData["is_budget_active"];
            $this->addBudget($Group);
        }

        $data = $this->dataPersistor->get('groups');
        if (!empty($data)) {
            $Group = $this->collection->getNewEmptyItem();
            $Group->setData($data);
            $groupData = $Group->getData();
            $this->loadedData[$Group->getId()]["group"] = $groupData;
            $this->loadedData[$Group->getId()]["budget"]["is_budget_active"]
                = $groupData["is_budget_active"];
            $this->dataPersistor->clear('groups');
        }

        return $this->loadedData;

    }//end getData()


    /**
     * Mapping data provider.
     *
     * @param mixed $connection Connection model.
     *
     * @return void
     */
    public function addBudget($Group)
    {
        $collectionBudget = $this->budgetCollection;
        $collectionBudget->addFieldToFilter('group_id', $Group->getId());

        $budgets = $collectionBudget->getItems();
        foreach ($budgets as $budget) {
            $budget = $budget->getData();
            $budget["end_date"] = $this->getEndDate($budget);
            $budget["amount"] = number_format($budget["amount"], 4) ;
            $this->loadedData[$Group->getId()]["budget"]["budget"][] = $budget;
        }

    }//end initDefaultMappings()

    /**
     * calculate end Date.
     *
     * @param $budget
     *
     * @return false|string
     */
    private function getEndDate($budget)
    {
        $startDate = $budget["start_date"];
        $duration = $budget["duration"];
        $budgetType = $budget["type"];
        switch ($budgetType) {
            case BudgetTypes::TYPE_DAILY:
                $strToTimeDate = $startDate." +".$duration." day";
                break;
            case BudgetTypes::TYPE_MONTHLY:
                $strToTimeDate = $startDate." +".$duration." month";
                break;
            case BudgetTypes::TYPE_QUARTERLY:
                $duration = $duration * 3;
                $strToTimeDate = $startDate." +".$duration." month";
                break;
            case BudgetTypes::TYPE_YEARLY:
                $strToTimeDate = $startDate." +".$duration." year";
                break;
            default:
                $strToTimeDate = "";
                break;
        }

        return date(
            "m/d/Y",
            strtotime($strToTimeDate)
        );

    }//end getEndDate()
}
