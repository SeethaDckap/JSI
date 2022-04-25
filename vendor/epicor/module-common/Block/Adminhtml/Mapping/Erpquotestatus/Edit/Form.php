<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Erpquotestatus\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{



    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Model\Config\Source\QuotestatusFactory
     */
    protected $customerconnectConfigSourceQuotestatusFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Model\Config\Source\QuotestatusFactory $customerconnectConfigSourceQuotestatusFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [])
    {
        $this->customerconnectConfigSourceQuotestatusFactory = $customerconnectConfigSourceQuotestatusFactory;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        if ($this->_session->getErpquoteStatusMappingData()) {
            $data = $this->_session->getErpquoteStatusMappingData();
            $this->_session->getErpquotestatusMappingData(null);
        } elseif ($this->registry->registry('erpquotestatus_mapping_data')) {
            $data = $this->registry->registry('erpquotestatus_mapping_data')->getData();
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

        $fieldset = $form->addFieldset(
            'mapping_form', array(
            'legend' => __('Mapping Information')
            )
        );

        $fieldset->addField(
            'code', 'text', array(
            'label' => __('Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'code',
            'note' => __('Erp Quote Status Code'),
            )
        );


        $fieldset->addField(
            'state', 'select', array(
            'name' => 'state',
            'label' => __('Erp Quote Status'),
            'class' => 'required-entry',
            'values' => $this->customerconnectConfigSourceQuotestatusFactory->create()->toOptionArray(),
            'required' => true,
            )
        );

        $data = $this->includeStoreIdElement($data);

        $form->setValues($data);

        return parent::_prepareForm();
    }

}
