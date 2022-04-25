<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Dashboard\Budgets;

use Epicor\Comm\Helper\DataFactory;
use Epicor\OrderApproval\Model\Budgets\Allocations;
use Magento\Framework\View\Element\Template\Context;
use Epicor\OrderApproval\Model\Budgets\Spend;
use Epicor\OrderApproval\Model\Budgets\BudgetSummaryConfig;

class ShopperBudgetAllocations extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE_MY_ACCOUNT_BUDGETS_VIEW  = 'Epicor_Customer::my_account_information_budgets_read';

    /**
     * @var Allocations
     */
    private $allocations;

    /**
     * @var bool
     */
    protected $isErpBudgets = false;

    /**
     * @var BudgetSummaryConfig
     */
    private $budgetSummaryConfig;

    /**
     * @var array
     */
    private $budgetAllocations;

    /**
     * @var DataFactory
     */
    private $commHelperFactory;

    /**
     * ShopperBudgetAllocations constructor.
     * @param DataFactory $commHelperFactory
     * @param BudgetSummaryConfig $budgetSummaryConfig
     * @param Allocations $allocations
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        DataFactory $commHelperFactory,
        BudgetSummaryConfig $budgetSummaryConfig,
        Allocations $allocations,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->allocations = $allocations;
        $this->budgetSummaryConfig = $budgetSummaryConfig;
        $this->commHelperFactory = $commHelperFactory;
    }

    /**
     * @return array
     */
    public function getShopperAllocations()
    {
        if (!$this->budgetAllocations) {
            $this->budgetAllocations = $this->allocations->getBudgetAllocations($this->isErpBudgets);
        }

        ksort($this->budgetAllocations);
        return $this->budgetAllocations;
    }

    /**
     * @return bool
     */
    protected function isMasquerading()
    {
        $commHelper = $this->commHelperFactory->create();

        return (bool) $commHelper->isMasquerading();
    }

    /**
     * Access Rights read protection.
     *
     * @return bool
     */
    public function isBudgetReadAllowed()
    {
        if (!$this->_isAccessAllowed(self::FRONTEND_RESOURCE_MY_ACCOUNT_BUDGETS_VIEW) || $this->isMasquerading()) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function getIsErpBudgets()
    {
        return $this->isErpBudgets;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHeaderHtml()
    {
        $header = $this->getLayout()
            ->createBlock('Epicor\OrderApproval\Block\Dashboard\Budgets\Renderer\Columns')
            ->setTemplate('Epicor_OrderApproval::dashboard/budgets/renderer/header.phtml');
        if (!$this->isErpBudgets) {
            $header->setData('is_erp_budgets', false);
        } else {
            $header->setData('is_erp_budgets', true);
        }
        $header->setData('budget_allocations', $this->getShopperAllocations());

        return $header->toHtml();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBodyHtml()
    {
        $header = $this->getLayout()
            ->createBlock('Epicor\OrderApproval\Block\Dashboard\Budgets\Renderer\Columns')
            ->setTemplate('Epicor_OrderApproval::dashboard/budgets/renderer/body.phtml');
        if (!$this->isErpBudgets) {
            $header->setData('is_erp_budgets', false);
        } else {
            $header->setData('is_erp_budgets', true);
        }
        $header->setData('budget_allocations', $this->getShopperAllocations());

        return $header->toHtml();
    }

    /**
     * @return bool
     */
    public function isGridActive()
    {
        $this->getShopperAllocations();
        if (!$this->allocations->isBudgetsActive() || !$this->getShopperAllocations()) {
            return false;
        }
        if ($this->isErpBudgets) {
            return $this->budgetSummaryConfig->isBudgetGridActive();
        }
        return true;
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        if ($this->isErpBudgets) {
            return 'erp-budgets-details';
        } else {
            return 'shopper-budgets-details';
        }
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->isBudgetReadAllowed()) {
            return parent::_toHtml();
        }

        return '';
    }
}
