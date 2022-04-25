<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Country\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{



    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Directory\Model\Config\Source\CountryFactory
     */
    protected $directoryConfigSourceCountryFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
         \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\Config\Source\CountryFactory $directoryConfigSourceCountryFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
    array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->directoryConfigSourceCountryFactory = $directoryConfigSourceCountryFactory;
        parent::__construct($context, $data);
    }

    protected function _prepareForm()
    {
        if ($this->_session->getCountryMappingData()) {
            $data = $this->_session->getCountryMappingData();
            $this->_session->getCountryMappingData(null);
        } elseif ($this->registry->registry('country_mapping_data')) {
            $data = $this->registry->registry('country_mapping_data')->getData();
        } else {
            $data = array();
        }

        $form = $this->formFactory->create( ['data' => [
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

        $fieldset->addField('magento_id', 'select', array(
            'label' => __('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'magento_id',
            'values' => $this->directoryConfigSourceCountryFactory->create()->toOptionArray(),
            'note' => __('Magento Code'),
        ));


        $fieldset->addField('erp_code', 'text', array(
            'label' => __('ERP Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'erp_code',
        ));

        $data = $this->includeStoreIdElement($data);

        $form->setValues($data);

        return parent::_prepareForm();
    }

}
