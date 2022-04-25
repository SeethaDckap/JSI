<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Invoicestatus\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
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
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        if ($this->_session->getInvoicestatusMappingData()) {
            $data = $this->_session->getInvoicestatusMappingData();
            $this->_session->getInvoicestatusMappingData(null);
        } elseif ($this->registry->registry('invoicestatus_mapping_data')) {
            $data = $this->registry->registry('invoicestatus_mapping_data')->getData();
        } else {
            $data = array();
        }

        $form = $this->formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
                'enctype' => 'multipart/form-data']
            ]
        );


        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('mapping_form', array(
            'legend' => __('Mapping Information')
        ));

        $fieldset->addField('code', 'text', array(
            'label' => __('Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'code',
            'note' => __('Invoice Status Code'),
        ));


        $fieldset->addField('status', 'text', array(
            'name' => 'status',
            'label' => __('Invoice Status'),
            'class' => 'required-entry',
            'values' => 'status',
            'required' => true,
            )
        );
//
//        $fieldset->addField('state', 'text',
//            array(
//                'name'      => 'state',
//                'label'     => Mage::helper('sales')->__('Invoice State'),
//                'class'     => 'required-entry',
//                'values'    => 'state',
//                'required'  => true,
//            )
//        );

        $data = $this->includeStoreIdElement($data);

        $form->setValues($data);

        return parent::_prepareForm();
    }

}
