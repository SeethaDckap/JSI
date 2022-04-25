<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Block\Group\Budgets;

use Epicor\OrderApproval\Model\Config\Budgets\Source\BudgetTypes;
use Epicor\OrderApproval\Ui\Component\Listing\Column\BudgetAction;

class Form extends \Magento\Backend\Block\Widget\Form
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
     * @var BudgetAction
     */
    private $budgetAction;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param BudgetAction $budgetAction
     * @param BudgetTypes $budgetTypes
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        BudgetAction $budgetAction,
        BudgetTypes $budgetTypes,
        array $data = []
    ) {
        $this->formFactory = $formFactory;

        parent::__construct(
            $context,
            $data
        );
        $this->budgetTypes = $budgetTypes;
        $this->budgetAction = $budgetAction;
        $this->setTemplate('Epicor_OrderApproval::budgets/tab/form.phtml');
    }

    /**
     * @return \Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab\BudgetForm
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $groupId = $this->getRequest()->getParam('id');
        $formValues = $this->getData('form_data');
        $budgetId = $formValues['id'] ?? '';
        $form = $this->formFactory->create(
            [
                'data' => [
                    'id' => 'add_budget_form',
                    'action' => $this->getUrl('epicor_orderapproval/budgets/save'),
                    'method' => 'post',
                    'class' => 'admin__scope-old'
                ]
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'add_erpaccount_budget',
            []
        );
        $type = $formValues['type'] ?? '';
        $fieldset->addField('budget_type', 'select', [
            'name' => 'budget_type',
            'label' => __('Budget Type'),
            'id' => 'budget_type',
            'title' => __('Budget Type'),
            'required' => true,
            'value' => strtolower($type),
            'values' => $this->budgetTypes->getShopperOptionValues($groupId, $type)
        ]);

        $fieldset->addField('start_date', 'date', [
            'label' => __('Start Date'),
            'title' => __('Start Date'),
            'required' => true,
            'name' => 'start_date',
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
            'disabled' => true,
            'name' => 'end_date',
            'comment' => 'Change Date Using Date Picker',
            'date_format' => 'yyyy-MM-dd',
            'class' => 'disabled'
        ]);

        $fieldset->addField('amount', 'text', [
            'label' => __('Budget Amount'),
            'title' => __('Budget Amount'),
            'required' => true,
            'name' => 'amount',
            'comment' => 'budget amount',
            'class' => 'currency-number'
        ]);

        $fieldset->addField('group_id', 'hidden', [
            'name' => 'group_id',
            'value' => $groupId
        ]);

        if ($budgetId) {
            $fieldset->addField('budget_id', 'hidden', [
                'name' => 'budget_id',
                'value' => $budgetId
            ]);
        }

        $fieldset->addField('is_allow_checkout', 'select', [
            'name' => 'is_allow_checkout',
            'label' => __('Budget Action'),
            'id' => 'budget_action_checkout',
            'title' => __('Budget Action'),
            'required' => true,
            'values' => $this->budgetAction->toOptionArray()
        ]);

        $fieldset->addField('add_budget_action', 'button', [
            'value' => __('Save Budget'),
            'name' => 'add_budget_action',
            'class' => 'form-button primary action'
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
            ->setTemplate('Epicor_OrderApproval::budgets/tab/duration-note.phtml')
            ->toHtml();
    }

    /**
     * @return string
     */
    public function getFormLoadUrl()
    {
        return $this->getUrl(
            'epicor_orderapproval/budgets/budgetform',
            ['id' => $this->getRequest()->getParam('id')]
        );
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'epicor_orderapproval/budgets/budgetsgrid',
            ['id' => $this->getRequest()->getParam('id')]
        );
    }
}
