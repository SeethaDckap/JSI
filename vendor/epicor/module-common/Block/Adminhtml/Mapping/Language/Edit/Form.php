<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Language\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{



    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Config\Model\Config\Source\LocaleFactory
     */
    protected $configConfigSourceLocaleFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Config\Model\Config\Source\LocaleFactory $configConfigSourceLocaleFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->configConfigSourceLocaleFactory = $configConfigSourceLocaleFactory;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        if ($this->_session->getLanguageMappingData()) {
            $data = $this->_session->getLanguageMappingData();
            $this->_session->getLanguageMappingData(null);
        } elseif ($this->registry->registry('language_mapping_data')) {
            $data = $this->registry->registry('language_mapping_data')->getData();
        } else {
            $data = array();
        }

        if (isset($data['language_codes']) && !is_array($data['language_codes'])) {
            $data['language_codes'] = explode(', ', $data['language_codes']);
        }

        $form = $this->formFactory->create(['data' => [
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data']
        ]);

        $form->setUseContainer(true);

        $this->setForm($form);

        $fieldset = $form->addFieldset('mapping_form', array(
            'legend' => __('Mapping Information')
        ));

        $fieldset->addField('erp_code', 'text', array(
            'label' => __('ERP Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'erp_code',
        ));

        $fieldset->addField('language_codes', 'multiselect', array(
            'label' => __('Locale Languages'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'language_codes',
            'values' => $this->configConfigSourceLocaleFactory->create()->toOptionArray(),
            'note' => __('Magento Code'),
        ));

        $data = $this->includeStoreIdElement($data);

        $form->setValues($data);

        return parent::_prepareForm();
    }

}
