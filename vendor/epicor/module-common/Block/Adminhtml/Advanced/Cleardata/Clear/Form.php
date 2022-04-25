<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Advanced\Cleardata\Clear;


/**
 * Epicor_Common_Block_Adminhtml_Advanced_Cleardata
 * 
 * Form for Clear Data
 * 
 * @category   Epicor
 * @package    Epicor_Common
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
    }
  
    protected function _prepareForm()
    {   
        $form = $this->formFactory->create(
           ['data' => [
                'id' => 'clear_form',
                'action' => $this->getUrl('*/*/clear'),
                'method' => 'post'
                ]
          ]);

        $form->setUseContainer(true);
        $this->setForm($form);
        
        $fieldset = $form->addFieldset('layout_block_form', array('legend' => __('Clear Data')));

        $fieldset->addField('products', 'checkbox', array(
            'label' => __('Products'),
            'name' => 'products',
            'value' => '1',
            'tabindex' => 1
        ));

        $fieldset->addField('categories', 'checkbox', array(
            'label' => __('Categories'),
            'name' => 'categories',
            'value' => '1',
            'tabindex' => 2
        ));

        $fieldset->addField('erpaccounts', 'checkbox', array(
            'label' => __('ERP Accounts'),
            'name' => 'erpaccounts',
            'value' => '1',
            'tabindex' => 3
        ));

        $fieldset->addField('customers', 'checkbox', array(
            'label' => __('Customers'),
            'name' => 'customers',
            'checked' => false,
            'value' => '1',
            'tabindex' => 4
        ));

        $fieldset->addField('quotes', 'checkbox', array(
            'label' => __('Quotes'),
            'name' => 'quotes',
            'checked' => false,
            'value' => '1',
            'tabindex' => 5
        ));

        $fieldset->addField('orders', 'checkbox', array(
            'label' => __('Orders'),
            'name' => 'orders',
            'checked' => false,
            'value' => '1',
            'tabindex' => 6
        ));

        $fieldset->addField('returns', 'checkbox', array(
            'label' => __('Returns'),
            'name' => 'returns',
            'checked' => false,
            'value' => '1',
            'tabindex' => 7
        ));

        $fieldset->addField('locations', 'checkbox', array(
            'label' => __('Locations'),
            'name' => 'locations',
            'checked' => false,
            'value' => '1',
            'tabindex' => 8
        ));

        $fieldset->addField('lists', 'checkbox', array(
            'label' => __('Lists'),
            'name' => 'lists',
            'checked' => false,
            'value' => '1',
            'tabindex' => 8
        ));

        return parent::_prepareForm();
    }

}
