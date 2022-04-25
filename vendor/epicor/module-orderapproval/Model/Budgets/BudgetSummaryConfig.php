<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Budgets;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;

class BudgetSummaryConfig
{
    const CUSTOMER_ACCOUNT_SUMMARY_CONFIG_PATH = 'customerconnect_enabled_messages/customer_account_summary/';
    const SHOW_BUDGET_TYPE_CONFIG = 'show_budget_type';
    const SHOW_BUDGET_ALLOCATED_CONFIG = 'show_budget_allocated';
    const SHOW_BUDGET_USED_CONFIG = 'show_budget_used';
    const SHOW_BUDGET_BALANCE_CONFIG = 'show_budget_balance';
    const SHOW_BUDGET_START_DATE_CONFIG = 'show_budget_start_date';
    const SHOW_BUDGET_END_DATE_CONFIG = 'show_budget_end_date';
    const GLOBAL_REQUEST_PRICE_PRECISION =  'epicor_comm_enabled_messages/global_request/price_precision';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * BudgetSummaryConfig constructor.
     * @param DataObjectFactory $dataObjectFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        ScopeConfigInterface $scopeConfig
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param array $data
     * @return \Magento\Framework\DataObject
     */
    private function getColumnData($data)
    {
        return $this->dataObjectFactory->create(['data' => $data]);
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            self::SHOW_BUDGET_TYPE_CONFIG => $this->getColumnData([
                'title' => 'Budget Type',
                'head_id' => $this->getHeadId(self::SHOW_BUDGET_TYPE_CONFIG),
                'active' => $this->isTypeActive()
            ]),
            self::SHOW_BUDGET_ALLOCATED_CONFIG => $this->getColumnData([
                'title' => 'Budget Allocated',
                'head_id' => $this->getHeadId(self::SHOW_BUDGET_ALLOCATED_CONFIG),
                'active' => $this->isAllocatedActive()
            ]),
            self::SHOW_BUDGET_USED_CONFIG => $this->getColumnData([
                'title' => 'Budget Used',
                'head_id' => $this->getHeadId(self::SHOW_BUDGET_USED_CONFIG),
                'active' => $this->isUsedActive()
            ]),
            self::SHOW_BUDGET_BALANCE_CONFIG => $this->getColumnData([
                'title' => 'Budget Balance',
                'head_id' => $this->getHeadId(self::SHOW_BUDGET_BALANCE_CONFIG),
                'active' => $this->isBalanceActive()
            ]),
            self::SHOW_BUDGET_START_DATE_CONFIG => $this->getColumnData([
                'title' => 'Start Date',
                'head_id' => $this->getHeadId(self::SHOW_BUDGET_START_DATE_CONFIG),
                'active' => $this->isStartDateActive()
            ]),
            self::SHOW_BUDGET_END_DATE_CONFIG => $this->getColumnData([
                'title' => 'End Date',
                'head_id' => $this->getHeadId(self::SHOW_BUDGET_END_DATE_CONFIG),
                'active' => $this->isEndDateActive()
            ])
        ];
    }

    /**
     * @param  string $type
     * @return string|string[]
     */
    private function getHeadId($type)
    {
        $colType = str_replace('show', 'col', $type);
        return str_replace('_', '-', $colType);
    }

    /**
     * @return bool
     */
    public function isBudgetGridActive()
    {
        return $this->isTypeActive()
            || $this->isAllocatedActive()
            || $this->isBalanceActive()
            || $this->isUsedActive()
            || $this->isStartDateActive()
            || $this->isEndDateActive();
    }

    /**
     * @return bool
     */
    public function isTypeActive()
    {
        return (bool) $this->getConfig(self::SHOW_BUDGET_TYPE_CONFIG);
    }

    /**
     * @return bool
     */
    public function isAllocatedActive()
    {
        return (bool) $this->getConfig(self::SHOW_BUDGET_ALLOCATED_CONFIG);
    }

    /**
     * @return bool
     */
    public function isUsedActive()
    {
        return (bool) $this->getConfig(self::SHOW_BUDGET_USED_CONFIG);
    }

    /**
     * @return int
     */
    public function getGlobalPricePrecisionConfig()
    {
        return (int) $this->scopeConfig->getValue(self::GLOBAL_REQUEST_PRICE_PRECISION);
    }

    /**
     * @return bool
     */
    public function isBalanceActive()
    {
        return (bool) $this->getConfig(self::SHOW_BUDGET_BALANCE_CONFIG);
    }

    /**
     * @return bool
     */
    public function isStartDateActive()
    {
        return (bool) $this->getConfig(self::SHOW_BUDGET_START_DATE_CONFIG);
    }

    /**
     * @return bool
     */
    public function isEndDateActive()
    {
        return (bool) $this->getConfig(self::SHOW_BUDGET_END_DATE_CONFIG) ;
    }

    /**
     * @param string $config
     * @return mixed
     */
    private function getConfig($config)
    {
        return $this->scopeConfig
            ->getValue(self::CUSTOMER_ACCOUNT_SUMMARY_CONFIG_PATH . $config);
    }
}