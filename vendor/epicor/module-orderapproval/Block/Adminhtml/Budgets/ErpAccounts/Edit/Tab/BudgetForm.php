<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab;

use Epicor\OrderApproval\Model\Config\Budgets\Source\BudgetTypes;

class BudgetForm extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var BudgetTypes
     */
    private $budgetTypes;

    /**
     * BudgetForm constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param BudgetTypes $budgetTypes
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        BudgetTypes $budgetTypes,
        array $data = []
    ) {
        $this->formFactory = $formFactory;

        parent::__construct(
            $context,
            $data
        );
        $this->budgetTypes = $budgetTypes;
    }

    /**
     * @return BudgetForm
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $erpId = $this->getRequest()->getParam('erp_id');
        $formValues = $this->getData('form_data');
        $budgetId = $formValues['budget_id'] ?? '';
        $form = $this->formFactory->create(
            [
                'data' => [
                    'id' => 'add_budget_form',
                    'action' => $this->getUrl('orderapproval/budgets_erpaccounts/save'),
                    'method' => 'post',
                    'class' => 'admin__scope-old'
                ]
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'add_erpaccount_budget',
            ['legend' => __('Budget Information')]
        );
        $type = $formValues['type'] ?? '';
        $fieldset->addField('budget_type', 'select', [
            'name' => 'budget_type',
            'label' => __('Budget Type'),
            'id' => 'budget_type',
            'title' => __('Budget Type'),
            'required' => true,
            'value' => $type,
            'values' => $this->budgetTypes->getErpOptionValues($erpId, $type)
            ]);

        $fieldset->addField('start_date', 'date', [
            'label' => __('Start Date'),
            'title' => __('Start Date'),
            'required' => true,
            'name' => 'from_date',
            'comment' => 'Change Date Using Date Picker',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'class' => 'datepicker validate-date',
            'date_format' => 'yyyy-MM-dd'
        ]);

        $fieldset->addField('duration', 'text', [
            'label' => __('Duration'),
            'title' => __('Duration'),
            'required' => true,
            'name' => 'duration',
            'class' => 'validate-digits',
            'comment' => 'Calculates the duration of the budget',
        ]);

        $fieldset->addField('duration_note', 'note', [
            'title' => __('Budget duration note'),
            'text' => $this->getDurationNote()
        ]);

        $fieldset->addField('end_date', 'date', [
            'label' => __('End Date'),
            'title' => __('End Date'),
            'required' => false,
            'name' => 'to_date',
            'comment' => 'Change Date Using Date Picker',
            'date_format' => 'yyyy-MM-dd',
            'class' => 'disabled'
        ]);

        $fieldset->addField('budget_amount', 'text', [
            'label' => __('Budget Amount'),
            'title' => __('Budget Amount'),
            'required' => true,
            'name' => 'budget_amount',
            'comment' => 'budget amount',
            'class' => 'currency-number'
        ]);

        $fieldset->addField('erp_id', 'hidden', [
            'name' => 'erp_id',
            'value' => $this->getRequest()->getParam('erp_id')
        ]);

        if ($budgetId) {
            $fieldset->addField('budget_id', 'hidden', [
                'name' => 'budget_id',
                'value' => $budgetId
            ]);
        }

        $fieldset->addField('erp_orders', 'checkbox', [
            'label' => __('Budget Includes ERP Orders'),
            'title' => __('Budget Includes ERP Orders'),
            'required' => false,
            'name' => 'erp_orders',
            'value' => 1,
            'checked' => isset($formValues['erp_orders']) && $formValues['erp_orders']
        ]);

        $fieldset->addField('budget_action', 'select', [
                'name' => 'budget_action_checkout',
                'label' => __('Budget Action'),
                'id' => 'budget_action_checkout',
                'title' => __('Budget Action'),
                'required' => true,
                'values' => [0 => 'Do Not Allow Checkout if Over budget']
            ]);

        $fieldset->addField('add_budget_action', 'button', [
                'value' => __('Save Budget'),
                'name' => 'add_budget_action',
                'class' => 'form-button primary'
            ]);

        $form->addValues($formValues);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDurationNote()
    {
        return $this->getLayout()
            ->createBlock('Magento\Backend\Block\Template')
            ->setTemplate('Epicor_OrderApproval::budgets/erpaccount/tab/duration-note.phtml')
            ->toHtml();
    }
}
