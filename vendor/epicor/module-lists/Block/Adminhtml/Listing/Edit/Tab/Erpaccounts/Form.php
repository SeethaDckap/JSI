<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Erpaccounts;


/**
 * List ERP Accounts Form
 *
 * @category   Epicor
 * @package    Epicor_Lists
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
     * Builds List ERP Accounts Form
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Erpaccounts\Form
     */
    protected function _prepareForm()
    {
        $list = $this->registry->registry('list');
        /* @var $list Epicor_Lists_Model_ListModel */

        if ($list->getTypeInstance()->isSectionEditable('erpaccounts')) {
            $form = $this->formFactory->create();
            $formData = $this->backendSession->getFormData(true);

            if (empty($formData)) {
                $formData = $list->getData();
            }

            $fieldset = $form->addFieldset('erpaccounts_form', array('legend' => __('Erp Accounts')));

            $fieldset->addField('erp_account_link_type', 'select', array(
                'label' => __('Erp Account Link Type'),
                'required' => false,
                'name' => 'erp_account_link_type',
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
                        'label' => __('No specific link'),
                        'value' => 'N',
                    ),
                    array(
                        'label' => __('Chosen ERP'),
                        'value' => 'E',
                    ),
                ),
            ))->setAfterElementHtml('<input type="hidden" value="' . $this->backendHelper->getUrl("epicor_lists/epicorlists_lists/erpaccountsessionset/", array()) . '" name="ajax_url" id="ajax_url" />');

            $checked = $list->getErpAccountsExclusion() == 'Y' ? true : false;
            $fieldset->addField('erp_accounts_exclusion', 'checkbox', array(
                'label' => __('Exclude selected ERP Accounts?'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'name' => 'erp_accounts_exclusion',
                'checked' => $checked
            ));

            $selectedErpAccount = $formData['erp_account_link_type'];
            if ($selectedErpAccount) {
                $this->backendAuthSession->setLinkTypeValue($selectedErpAccount);
            } else {
                $this->backendAuthSession->setLinkTypeValue('');
            }
            $form->setValues($formData);
            $this->setForm($form);
        }

        return parent::_prepareForm();
    }

}
