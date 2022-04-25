<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab;

use Epicor\OrderApproval\Model\Budgets\ErpBudgets as CustomerErpAccount;

class BudgetConfig extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var CustomerErpAccount
     */
    private $customerErpAccount;

    /**
     * BudgetConfig constructor.
     * @param CustomerErpAccount $customerErpAccount
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        CustomerErpAccount $customerErpAccount,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->backendSession = $context->getBackendSession();
        parent::__construct(
            $context,
            $data
        );
        $this->_title = 'ERP Accounts';
        $this->customerErpAccount = $customerErpAccount;
    }

    /**
     * @return BudgetConfig
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $erpId = $this->getRequest()->getParam('erp_id');
        $erpAccount = $this->customerErpAccount->getErpAccount($erpId);

        $form = $this->formFactory->create();

        $fieldset = $form->addFieldset('budget_config_form', array('legend' => __('Active Details')));

        $fieldset->addField('is_budget_active', 'checkbox', [
            'label' => __('Is Active'),
            'title' => __('Is Active'),
            'required' => false,
            'name' => 'is_budget_active',
            'value' => 1,
            'checked' => $erpAccount->getData('is_budget_active') ? true : false
        ]);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
