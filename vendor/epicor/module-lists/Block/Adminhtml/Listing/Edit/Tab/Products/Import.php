<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products;


/**
 * List ERP Accounts Form
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Import extends \Magento\Backend\Block\Widget\Form
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
    )
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
        $this->_title = 'Import Products';
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

        $form = $this->formFactory->create();

        if ($list->getTypeInstance()->isSectionEditable('products')) {
            $fieldset = $form->addFieldset('import_fields', array('legend' => __('Product Import')));
            /* @var $fieldset Varien_Data_Form_Element_Fieldset */

            $fieldset->addField('productimportcsv', 'button', array(
                'value' => __('Download Example CSV File'),
                'onclick' => "return listProduct.dowloadCsv();",
                'name' => 'productimportcsv',
                'class' => 'form-button'
            ));

            $fieldset->addField(
                'import',
                'file',
                array(
                'label' => __('CSV File'),
                'name' => 'import',
                'accept' => '.csv',
                'note' => __('CSV containing 6 columns: “SKU", "UOM", "Currency", "Price", "Break Quantity", "Break price", "Description"')
                )
            );

            $fieldset->addField('importSubmit', 'button', array(
                'value' => __('Import'),
                'onclick' => "return listProduct.import();",
                'name' => 'importSubmit',
                'class' => 'form-button'
            ));

            $fieldset->addField('deleteAllProducts', 'checkbox', array(
                'label' => __('Delete existing prices and products before import'),
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'name' => 'deleteAllProducts',
                'value' => '0',
            ));
        }

        if ($list->getTypeInstance()->isSectionEditable('pricing')) {
            $form->addField('json_pricing', 'hidden', array(
                'name' => 'json_pricing',
            ));
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
