<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Block\Adminhtml\Roles\Edit\Tab\Erpaccounts;


/**
 * Role ERP Accounts Form
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
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
        $this->_title = 'ERP Accounts';
    }

    /**
     * Accounts Form
     *
     * @return \Magento\Backend\Block\Widget\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $role = $this->registry->registry('role');
        /* @var $role \Epicor\AccessRight\Model\RoleModel */

        $form = $this->formFactory->create();
        $formData = $this->backendSession->getFormData(true);

        if (empty($formData)) {
            $formData = $role->getData();
        }

        $fieldset = $form->addFieldset('erpaccounts_form', array('legend' => __('Erp Accounts')));

        $fieldset->addField('erp_account_link_type', 'select', array(
            'label' => __('Erp Account Link Type'),
            'required' => false,
            'name' => 'erp_account_link_type',
            'onchange' => "epraccount.changeERPType()",
            'values' => array(
                array(
                    'label' => __('B2C'),
                    'value' => 'C',
                ),
                array(
                    'label' => __('B2B'),
                    'value' => 'B',
                ),
                array(
                    'label' => __('Dealer'),
                    'value' => 'R',
                ),
                array(
                    'label' => __('Distributor'),
                    'value' => 'D',
                ),
                array(
                    'label' => __('Supplier'),
                    'value' => 'S',
                ),
                array(
                    'label' => __('No specific link'),
                    'value' => 'N',
                ),
                array(
                    'label' => __('Chosen ERP'),
                    'value' => 'E',
                ),
            ),
        ))->setAfterElementHtml('<input type="hidden" value="' . $this->backendHelper->getUrl("*/*/erpaccountsessionset/", array()) . '" name="ajax_url" id="ajax_url" />');

        $checked = $role->getErpAccountsExclusion() == 'Y' ? true : false;
        $fieldset->addField('erp_accounts_exclusion', 'checkbox', array(
            'label' => __('Exclude selected ERP Accounts?'),
            'onclick' => 'this.value = this.checked ? 1 : 0;',
            'name' => 'erp_accounts_exclusion',
            'checked' => $checked
        ));

        $selectedErpAccount = isset($formData['erp_account_link_type']) ? $formData['erp_account_link_type'] : null;
        if ($selectedErpAccount) {
            $this->backendAuthSession->setLinkTypeValue($selectedErpAccount);
        } else {
            $this->backendAuthSession->setLinkTypeValue('');
        }

        $conditions = $role->getErpAccountsConditions();
        $fieldset->addField('is_erp_account_condition_enabled', 'checkbox', array(
            'label' => __('ERP account to role conditionally?'),
            'onclick' => 'this.value = this.checked ? 1 : 0;',
            'name' => 'is_erp_account_condition_enabled',
            'checked' => $conditions ? true : false
        ));

        if($formData) {
            $form->setValues($formData);
        } else { // New Role
            $form->setValues(array("erp_account_link_type"=>'N',"erp_accounts_exclusion"=>'0'));
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

}
