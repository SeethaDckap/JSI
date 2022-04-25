<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage\Children;


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
        $fieldset = $form->addFieldset('sales_rep_form', array('legend' => __('Add Child Account')));

        $fieldset->addField('sales_rep_id', 'text', array(
            'label' => __('Sales Rep Account Number'),
            'required' => true,
            'name' => 'child_sales_rep_account_id',
        ));

        $fieldset->addField('name', 'text', array(
            'label' => __('Name'),
            'required' => true,
            'name' => 'child_sales_rep_account_name'
        ));

        $fieldset->addField('addChildAccount', 'submit', array(
            'value' => __('Add'),
            'name' => 'addChildAccount',
            'class' => 'form-button',
        ));

        $this->setForm($form);
    }

}
