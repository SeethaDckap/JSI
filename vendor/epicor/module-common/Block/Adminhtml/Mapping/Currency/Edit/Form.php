<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Currency\Edit;


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

    /**
     * @var \Magento\Config\Model\Config\Source\Locale\Currency
     */
    protected $currency;


    public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\Data\FormFactory $formFactory,
    \Magento\Config\Model\Config\Source\Locale\Currency $currency,
    array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->currency = $currency;
        parent::__construct($context, $data);

    }

    protected function _prepareForm()
    {
        if ($this->_session->getCurrencyMappingData()) {
            $data = $this->_session->getCurrencyMappingData();
            $this->_session->getCurrencyMappingData(null);
        } elseif ($this->registry->registry('currency_mapping_data')) {
            $data = $this->registry->registry('currency_mapping_data')->getData();
        } else {
            $data = array();
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

        $fieldset->addField('magento_id', 'select', array(
            'label' => __('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'magento_id',
            //M1 > M2 Translation Begin (Rule p2-1)
            //'values' => Mage::getModel('adminhtml/system_config_source_currency')->toOptionArray(false),
            'values' => $this->currency->toOptionArray(false),
            //M1 > M2 Translation End
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
