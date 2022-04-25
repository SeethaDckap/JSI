<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access\Group\Edit\Tab;


/**
 * 
 * Access group detail edit tab
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Details extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function getGroup()
    {

        if (!$this->_accessright) {
            $this->_accessright = $this->registry->registry('access_group_data');
        }
        return $this->_accessright;
    }

    protected function _prepareForm()
    {
        $form = $this->formFactory->create();
        $this->setForm($form);
        $fieldset = $form->addFieldset('form_form', array('legend' => __('Item information')));

        $fieldset->addType('account_selector', 'Epicor_Comm_Block_Adminhtml_Form_Element_Erpaccount');

        $fieldset->addField('entity_name', 'text', array(
            'label' => __('Access Group'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'entity_name',
        ));

        $fieldset->addField('type', 'select', array(
            'label' => __('Type'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'type',
            'values' => array('B2B' => 'B2B', 'B2C' => 'B2C', 'supplier' => 'Supplier')
        ));

        $fieldset->addField('erp_account_id', 'account_selector', array(
            'label' => __('ERP Account'),
            'name' => 'erp_account_id'
        ));

        $form->setValues($this->getGroup());
        return parent::_prepareForm();
    }

}
