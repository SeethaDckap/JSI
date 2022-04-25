<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage\Salesreps;


/**
 * Sales Rep Account 
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        parent::__construct(
            $context,
            $data
        );
        $this->setTemplate('Epicor_Common::widget/grid/form.phtml');
    }


    protected function _prepareForm()
    {
        $form = $this->formFactory->create();

        $this->setForm($form);


        $fieldset = $form->addFieldset('sales_rep_form', array('legend' =>__('Add Sales Rep')));

        $fieldset->addField('sales_rep_id', 'text', array(
            'label' =>__('Sales Rep ID'),
            'required' => false,
            'name' => 'sales_rep_id',
        ));

        $fieldset->addField('first_name', 'text', array(
            'label' =>__('First Name'),
            'required' => true,
            'name' => 'first_name',
        ));

        $fieldset->addField('last_name', 'text', array(
            'label' =>__('Last Name'),
            'required' => true,
            'name' => 'last_name',
        ));

        $fieldset->addField('email_address', 'text', array(
            'label' =>__('Email Address'),
            'required' => true,
            'name' => 'email_address',
            'class' => 'validate-email'
        ));

        $fieldset->addField('addSalesRep', 'submit', array(
            'value' =>__('Add'),
            'name' => 'addSalesRep',
            'class' => 'form-button',
        ));

        $this->setForm($form);
    }

}
