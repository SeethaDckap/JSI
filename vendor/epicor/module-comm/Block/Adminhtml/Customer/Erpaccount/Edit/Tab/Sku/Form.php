<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Sku;


/**
 * Description of Form
 *
 * @author Guillermo.Garza
 */
class Form extends \Magento\Backend\Block\Widget\Form
{
    protected $_erp_customer;

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
    )
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
        $this->setId('customer_sku_edit_form');
    }

    public function getErpCustomer()
    {
        if (!$this->_erp_customer) {
            $this->_erp_customer = $this->registry->registry('customer_erp_account');
        }
        return $this->_erp_customer;
    }

    protected function _prepareForm()
    {

        $form = $this->formFactory->create();
        $fieldset = $form->addFieldset('customer_sku_form', array('legend' => __('SKU')));
        $fieldset->setHeaderBar(
            '<button title="' . __('Close') . '" type="button" class="scalable" onclick="customerSku.close();"><span><span><span>' . __('Close') . '</span></span></span></button>'
        );

        $fieldset->addField('customersku_post_url', 'hidden', array(
            'name' => 'customersku_post_url',
            'value' => $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/customerskupost')
        ));

        $fieldset->addField('customersku_delete_url', 'hidden', array(
            'name' => 'customersku_delete_url',
            'value' => $this->getUrl('adminhtml/epicorcomm_customer_erpaccount/customerskudelete')
        ));

        $fieldset->addField('entity_id', 'hidden', array(
            'name' => 'entity_id',
        ));

        $fieldset->addField('customer_group_id', 'hidden', array(
            'name' => 'customer_group_id',
            'value' => $this->getErpCustomer()->getId()
        ));

        $fieldset->addField('product_id', 'text', array(
            'label' => __('Product'),
            'name' => 'product_id',
            'value' => 'default',
            'required' => true
        ));

        $fieldset->addField('sku', 'text', array(
            'label' => __('Customer SKU'),
            'name' => 'sku',
            'value' => 'default',
            'required' => true
        ));
        $fieldset->addField('description', 'text', array(
            'label' => __('Description'),
            'name' => 'description',
            'value' => 'default',
            //'required' => true
        ));

        $fieldset->addField('updateCustomerSkuSubmit', 'submit', array(
            'value' => __('Update'),
            'onclick' => "return customerSku.save();",
            'name' => 'updateCustomerSkuSubmit',
            'class' => 'form-button'
        ));

        $fieldset->addField('addCustomerSkuSubmit', 'submit', array(
            'value' => __('Add'),
            'onclick' => "return customerSku.save();",
            'name' => 'addCustomerSkuSubmit',
            'class' => 'form-button'
        ))->setAfterElementHtml("
            <script type=\"text/javascript\">
                createCustomerSku('customer_sku_form','customer_sku_grid');
            </script>");

        $this->setForm($form);
    }

}
