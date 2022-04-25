<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Block\Adminhtml\Roles\Edit\Tab;

/**
 * List ERP Accounts Form
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
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

    /**
     * Import constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
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
        $this->_title = 'Import';
    }

    /**
     * Builds List ERP Accounts Form
     *
     * @return \Magento\Backend\Block\Widget\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $role = $this->registry->registry('role');
        /* @var $role \Epicor\AccessRight\Model\RoleModel */

        $form = $this->formFactory->create();

        //Erp tab
        if ($this->getImportFor() == "erpaccounts") {
            $fieldset = $form->addFieldset('erp_import_fields', array('legend' => __('ERP Import')));
            /* @var $fieldset Varien_Data_Form_Element_Fieldset */

            $fieldset->addField('erp_sample', 'button', array(
                'value' => __('Download Example CSV File'),
                'onclick' => "return epraccount.dowloadCsv();",
                'name' => 'erp_sample',
                'class' => 'form-button'
            ));

            $fieldset->addField('import', 'file', array(
                'label' => __('CSV File'),
                'name' => 'import',
                'note' => __('CSV containing 1 columns: "ERP Short Code" (required)')
            ));

            $fieldset->addField('importSubmit', 'button', array(
                'value' => __('Import'),
                'onclick' => "return epraccount.import();",
                'name' => 'importSubmit',
                'class' => 'form-button'
            ));

        } elseif ($this->getImportFor() == "customer") { //Customer tab
            $fieldset = $form->addFieldset('erp_import_fields', array('legend' => __('Customers Import')));
            /* @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */

            $fieldset->addField('customer_sample', 'button', array(
                'value' => __('Download Example CSV File'),
                'onclick' => "return customer.dowloadCsv();",
                'name' => 'customer_sample',
                'class' => 'form-button'
            ));

            $fieldset->addField('import_customer_media', 'file', array(
                'label' => __('CSV File'),
                'name' => 'import_customer_media',
                'note' => __('CSV containing 1 columns: "Customer Email Address" (required)')
            ));

            $fieldset->addField('import_customer', 'button', array(
                'value' => __('Import'),
                'onclick' => "return customer.import();",
                'name' => 'import_customer',
                'class' => 'form-button'
            ));
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
