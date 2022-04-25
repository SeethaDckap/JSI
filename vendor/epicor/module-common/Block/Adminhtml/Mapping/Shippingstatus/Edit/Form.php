<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Shippingstatus\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{



    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ShippingFactory
     */
    protected $commErpMappingShippingFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Erp\Mapping\ShippingFactory $commErpMappingShippingFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->commErpMappingShippingFactory = $commErpMappingShippingFactory;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        if ($this->_session->getShippingmethodsMappingData()) {
            $data = $this->_session->getShippingmethodsMappingData();
            $this->_session->getShippingmethodsMappingData(null);
        } elseif ($this->registry->registry('shippingstatus_mapping_data')) {
            $data = $this->registry->registry('shippingstatus_mapping_data')->getData();
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
        $fieldset->addField('shipping_status_code', 'text', array(
            'label' => __('Ship Status Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'shipping_status_code',
            'note' => __('Ship Status code'),
        ));

        $fieldset->addField('description', 'text', array(
            'name' => 'description',
            'label' => __('Ship Status Description'),
            'class' => 'required-entry',
            'required' => true,
                )
        );

        $fieldset->addField('status_help', 'textarea', array(
            'name' => 'status_help',
            'label' => __('Ship Status Help'),
            'class' => 'required-entry',
            'required' => true,
                // 'renderer'=>'customerconnect/adminhtml_mapping_shipstatus_renderer_textarea',
                )
        );
        $is_deafult = isset($data['is_default']) ? $data['is_default'] : 1;
        $fieldset->addField('is_default', 'checkbox', array(
            'label' => __('Is Default'),
            'name' => 'is_default',
            'value' => array(0, 1),
            'checked' => ($is_deafult == 1) ? 1 : 0,
            'onclick' => 'this.value = this.checked ? 1 : 0;',
                //'disabled' => false,
                //'readonly' => false,
        ));
        $data = $this->includeStoreIdElement($data);
        $form->setValues($data);

        return parent::_prepareForm();
    }

}
