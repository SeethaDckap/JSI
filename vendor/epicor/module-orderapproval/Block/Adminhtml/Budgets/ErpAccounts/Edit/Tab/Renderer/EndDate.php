<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer as WidgetAbstractRenderer;
use Epicor\OrderApproval\Model\Budgets\BudgetTypes;

class EndDate extends WidgetAbstractRenderer
{
    /**
     * @var BudgetTypes
     */
    private $budgetTypes;

    /**
     * EndDate constructor.
     * @param BudgetTypes $budgetTypes
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        BudgetTypes $budgetTypes,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->budgetTypes = $budgetTypes;
    }

    /**
     * Renders grid column End date
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $date = $this->budgetTypes->calculateSavedEndDate(
            $row->getData('start_date'),
            $row->getData('duration'),
            ucfirst($row->getData('type'))
        );

        $formatDate = strtotime($date);
        return date('Y-m-d', $formatDate);
    }
}