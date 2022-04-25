<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Dashboard\Budgets\Renderer;

use Epicor\AccessRight\Block\Template;
use Epicor\OrderApproval\Model\Budgets\Spend;
use Magento\Framework\View\Element\Template\Context as Context;
use Epicor\OrderApproval\Model\Budgets\BudgetSummaryConfig;
use Magento\Framework\DataObject;
use Epicor\OrderApproval\Model\Budgets\Allocations;

class Columns extends \Magento\Framework\View\Element\Template
{
    /**
     * @var BudgetSummaryConfig
     */
    private $budgetSummaryConfig;

    /**
     * @var string
     */
    private $class = 'data-grid-th';

    /**
     * @var Allocations
     */
    private $allocations;

    /**
     * Columns constructor.
     * @param Allocations $allocations
     * @param BudgetSummaryConfig $budgetSummaryConfig
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Allocations $allocations,
        BudgetSummaryConfig $budgetSummaryConfig,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->budgetSummaryConfig = $budgetSummaryConfig;
        $this->allocations = $allocations;
    }

    /**
     * @return array[]
     */
    public function getColumns()
    {
        return $this->budgetSummaryConfig->getColumns();
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    public function isGridActive()
    {
        if ($this->getData('is_erp_budget')) {
            return $this->budgetSummaryConfig->isBudgetGridActive();
        }
        return true;
    }

    /**
     * @param DataObject $column
     * @return bool
     */
    public function isColumnActive($column)
    {
        if ($this->getData('is_erp_budgets')) {
            return $column->getData('active');
        }
        return true;
    }

    /**
     * @param Spend $spend
     * @param DataObject $column
     * @return int|mixed|string
     */
    public function getColumnData($spend, $column)
    {
        $colType = $column->getData('head_id');
        switch ($colType) {
            case 'col-budget-type':
                $data = ucfirst($spend->getBudgetType());
                break;
            case 'col-budget-allocated':
                $data = $this->getNumberFormat($spend->getAllocated());
                break;
            case 'col-budget-used':
                $data = $this->getNumberFormat($this->getUsed($spend));
                break;
            case 'col-budget-balance':
                $data = $this->getNumberFormat($this->getRemaining($spend));
                break;
            case 'col-budget-start-date':
                $data = $spend->getStartDate();
                break;
            case 'col-budget-end-date':
                $data = $spend->getEndDate();
                break;
            default:
                $data = '';
        }

        return $data;
    }

    /**
     * @param string $value
     * @return string
     */
    private function getNumberFormat($value)
    {
        $precision = $this->budgetSummaryConfig->getGlobalPricePrecisionConfig();

        return number_format($value, $precision);
    }

    /**
     * @param Spend $spend
     * @return mixed
     */
    public function getUsed($spend)
    {
        if ($spend->getBudgetsExceeded() || $spend->getBalance() < 0) {
            return $spend->getAllocated();
        }

        return $spend->getUsed();
    }

    /**
     * @param Spend $spend
     * @return int
     */
    public function getRemaining($spend)
    {
        if ($spend->getBudgetsExceeded() || $spend->getBalance() < 0) {
            return 0;
        }

        return $spend->getBalance();
    }
}
