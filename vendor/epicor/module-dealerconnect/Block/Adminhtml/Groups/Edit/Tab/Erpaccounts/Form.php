<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Groups\Edit\Tab\Erpaccounts;


/**
 * Dealer Accounts Form
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->backendSession = $context->getBackendSession();
        $this->backendHelper = $backendHelper;
        $this->backendAuthSession = $backendAuthSession;
        parent::__construct(
            $context,
            $data
        );
        $this->_title = 'Dealer Accounts';
    }

    /**
     * Builds Dealer Accounts Form
     *
     * @return \Epicor\Dealerconnect\Block\Adminhtml\Groups\Edit\Tab\Erpaccounts\Form
     */
    protected function _prepareForm()
    {
        $dealerGrp = $this->registry->registry('dealergrp');
        /* @var $dealerGrp Epicor_Dealerconnect_Model_Dealergroups */

            $form = $this->formFactory->create();
            $formData = $this->backendSession->getFormData(true);

            if (empty($formData)) {
                $formData = $dealerGrp->getData();
            }

            $fieldset = $form->addFieldset('erpaccounts_form', array('legend' => __('Dealer Accounts')));

            $checked = $dealerGrp->getDealerAccountsExclusion() == 'Y' ? true : false;
            $fieldset->addField('erp_accounts_exclusion', 'checkbox', array(
                'label' => __('Exclude selected Dealer Accounts?'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'name' => 'erp_accounts_exclusion',
                'checked' => $checked
            ));

            $this->backendAuthSession->setLinkTypeValue('');
            $form->setValues($formData);
            $this->setForm($form);

        return parent::_prepareForm();
    }

}
