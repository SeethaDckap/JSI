<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Budgets;

use Epicor\OrderApproval\Model\Budget;
use Epicor\OrderApproval\Model\Budgets\BudgetTypes;
use Epicor\OrderApproval\Model\Groups\Budget as GroupBudget;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Budget as BudgetResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use DateTime;
use Epicor\OrderApproval\Model\Budgets\Period as BudgetPeriod;
use Epicor\OrderApproval\Model\Budgets\BudgetInterval;
use Epicor\OrderApproval\Model\Budgets\PeriodFactory;
use Epicor\OrderApproval\Model\Groups\Budget as OrderBudget;

class Orders
{
    /**
     * @var Budget
     */
    private $budget;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var BudgetResource
     */
    private $budgetResource;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Epicor\OrderApproval\Model\Budgets\BudgetTypes
     */
    private $budgetTypes;
    /**
     * @var \Epicor\OrderApproval\Model\Budgets\PeriodFactory
     */
    private $periodFactory;


    /**
     * Orders constructor.
     * @param \Epicor\OrderApproval\Model\Budgets\PeriodFactory $periodFactory
     * @param \Epicor\OrderApproval\Model\Budgets\BudgetTypes $budgetTypes
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Budget $budget
     * @param ResourceConnection $resourceConnection
     * @param BudgetResource $budgetResource
     */
    public function __construct(
        PeriodFactory $periodFactory,
        BudgetTypes $budgetTypes,
        OrderCollectionFactory $orderCollectionFactory,
        Budget  $budget,
        ResourceConnection $resourceConnection,
        BudgetResource $budgetResource
    ) {

        $this->budget = $budget;
        $this->resourceConnection = $resourceConnection;
        $this->budgetResource = $budgetResource;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->budgetTypes = $budgetTypes;
        $this->periodFactory = $periodFactory;
    }

    /**
     * @param array $customerIds
     * @param GroupBudget $budget
     * @param boolean $includeErp
     * @return OrderCollection
     */
    public function getBudgetOrders($customerIds, $budget, $includeErp = false)
    {
        $collection = $this->getOrderCollection();

        $this->filterCustomers($collection, $customerIds);
        $this->filterExcludeStatus($collection, $includeErp);
        $this->filterBudgetPeriod($collection, $budget);

        return $collection;
    }

    /**
     * @param OrderCollection $collection
     * @param array $customerIds
     */
    private function filterCustomers($collection, $customerIds)
    {
        $collection->addFieldToFilter('customer_id', ['in' => $customerIds]);
    }

    /**
     * @param OrderCollection $collection
     * @param boolean $includeErp
     * @return void
     */
    private function filterExcludeStatus($collection, $includeErp)
    {
        if (!$includeErp) {
            $collection->addFieldToFilter('ecc_gor_sent', ['nin' => $this->budgetResource->getExcludeOrderStatus()]);
        } else {
            $collection->addFieldToFilter('ecc_gor_sent', ['in' => $this->budgetResource->getIncludeOrderStatus()]);
        }
    }

    /**
     * @param OrderCollection $collection
     * @param GroupBudget $budget
     * @return void
     */
    private function filterBudgetPeriod($collection, $budget)
    {
        try {
            /** @var BudgetInterval  $currentInterval */
            $currentInterval = $this->getCurrentBudgetInterval($budget);
            $start = $currentInterval->getIntervalStart();
            $end = $currentInterval->getIntervalEnd();
            if ($start && $end) {
                $collection->addFieldToFilter('created_at', ['gteq' => $start])
                    ->addFieldToFilter('created_at', ['lteq' => $end]);
            } else {
                throw new \InvalidArgumentException(
                    'Error: unable to filter budget orders, start and end dates not available'
                );
            }
        } catch (\Exception $e) {
            $this->budgetTypes->getLogger()->error($e->getMessage());
        }
    }

    /**
     * @param \Epicor\OrderApproval\Model\Groups\Budget $budget
     * @return \Epicor\OrderApproval\Model\Budgets\BudgetInterval
     */
    private function getCurrentBudgetInterval($budget)
    {
        try {
            $budgetPeriod = $this->getBudgetPeriod($budget);
            return $budgetPeriod->getCurrentPeriodInterval();
        } catch (\Exception $e) {
            $this->budgetTypes->getLogger()->error($e->getMessage());
        }
    }

    /**
     * @param OrderBudget $budget
     * @return BudgetPeriod
     */
    public function getBudgetPeriod($budget)
    {
        $startDate = $budget->getStartDate();
        $duration = $budget->getDuration();
        $type = $budget->getType();
        return $this->periodFactory->create(
            ['data' => ['budgetStart' => $startDate, 'duration' => $duration, 'type' => $type]]
        );
    }

    /**
     * @param OrderCollection $collection
     */
    private function filterOrderToday($collection)
    {
        $start = self::dateTodayStart();
        $end = self::dateTomorrowStart();
        $collection->addFieldToFilter('created_at', ['gteq' => $start])
            ->addFieldToFilter('created_at', ['lt' => $end]);
    }

    /**
     * @return OrderCollection
     */
    private function getOrderCollection()
    {
        /** @var OrderCollection $collection */
        return $this->orderCollectionFactory->create();
    }

    /**
     * @param array $customerIds
     * @param boolean $includeErp
     * @return OrderCollection
     */
    public function getCustomerOrdersToday($customerIds, $includeErp = false)
    {
        $collection = $this->getOrderCollection();
        $this->filterCustomers($collection, $customerIds);
        $this->filterExcludeStatus($collection, $includeErp);
        $this->filterOrderToday($collection);

        return $collection;
    }

    /**
     * @param GroupBudget $budget
     * @return mixed
     */
    public function isBudgetValidToday($budget)
    {
        $budgetStart = $budget->getStartDate();
        $duration = $budget->getDuration();
        $type = $budget->getType();

        $endDate = $this->budgetTypes->calculateSavedEndDate($budgetStart, $duration, $type);
        $end = new DateTime($endDate);
        $now = new DateTime();
        $start = new DateTime($budgetStart);

        return $start <= $now && $now <= $end;
    }

    /**
     * @return false|string
     */
    public static function dateTodayStart()
    {
        return date('Y-m-d H:i:s', strtotime('today midnight'));
    }

    /**
     * @return false|string
     */
    public static function dateTomorrowStart()
    {
        return date('Y-m-d H:i:s', strtotime('tomorrow midnight'));
    }
}
