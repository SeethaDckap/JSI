<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access\Right\Edit\Tab;


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


    public function getRight()
    {

        if (!$this->_accessright) {
            $this->_accessright = $this->registry->registry('access_right_data');
        }
        return $this->_accessright;
    }

    protected function _prepareForm()
    {
        $form = $this->formFactory->create();
        $this->setForm($form);
        $fieldset = $form->addFieldset('form_form', array('legend' => __('Item information')));

        $fieldset->addField('entity_name', 'text', array(
            'label' => __('Access Right'),
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


        $form->setValues($this->getRight());
        return parent::_prepareForm();
    }

}
