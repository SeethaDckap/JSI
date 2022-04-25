<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Reasoncode\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{



    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\ReasoncodetypesFactory
     */
    protected $customerconnectErpMappingReasoncodetypesFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Customerconnect\Model\Erp\Mapping\ReasoncodetypesFactory $customerconnectErpMappingReasoncodetypesFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [])
    {$this->customerconnectErpMappingReasoncodetypesFactory = $customerconnectErpMappingReasoncodetypesFactory;
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        if ($this->_session->getReasoncodeMappingData()) {
            $data = $this->_session->getReasoncodeMappingData();
            $this->_session->getReasoncodeMappingData(null);
        } elseif ($this->registry->registry('reasoncode_mapping_data')) {
            $data = $this->registry->registry('reasoncode_mapping_data')->getData();
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
            'label' => __('Reason Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'code',
            'note' => __('Code'),
        ));

        $fieldset->addField('description', 'text', array(
            'name' => 'description',
            'label' => __('Reason Code Description'),
            'class' => 'required-entry',
            'values' => 'status',
            'required' => true,
            )
        );

        $fieldset->addField('type', 'select', array(
            'label' => __('Reason Code Type'),
            'name' => 'type',
            'values' => $this->customerconnectErpMappingReasoncodetypesFactory->create()->toOptionArray(),
        ));

        $data = $this->includeStoreIdElement($data);

        $form->setValues($data);

        return parent::_prepareForm();
    }

}
