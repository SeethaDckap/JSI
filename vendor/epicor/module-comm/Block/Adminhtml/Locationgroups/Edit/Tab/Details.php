<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locationgroups\Edit\Tab;

class  Details  extends  \Magento\Backend\Block\Widget\Form\Generic implements  \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\Data\Form\Element\SelectFactory
     */
    protected $formElementSelectFactory;
    /**
     * @var \Magento\Directory\Model\Config\Source\CountryFactory
     */
    protected $directoryConfigSourceCountryFactory;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryData;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Data\Form\Element\SelectFactory $formElementSelectFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Directory\Model\Config\Source\CountryFactory $directoryConfigSourceCountryFactory,
        array $data = [])
    {
        $this->directoryConfigSourceCountryFactory=$directoryConfigSourceCountryFactory;
        $this->formElementSelectFactory=$formElementSelectFactory;
        $this->directoryData=$directoryData;
        $this->registry = $registry;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * Initialize form
     *
     */
    public function initForm()
    {
        $group = $this->registry->registry('group');
        $form = $this->_formFactory->create();

        $this->setForm($form);

        $fieldset = $form->addFieldset('details', array('legend' => __('Details')));

        $fieldset->addField('group_name', 'text', array(
            'label' => __('Group Name'),
            'required' => true,
            'name' => 'group_name'
        ));
        $fieldset->addField('group_expandable', 'select', array(
            'label' => __('Group Expandable'),
            'name' => 'group_expandable',
            'options' => array(
                1 => __('Yes'),
                0 => __('No')
            )
        ));
        $fieldset->addField('show_aggregate_stock', 'select', array(
            'label' => __('Show Aggregate Stock'),
            'name' => 'show_aggregate_stock',
            'options' => array(
                1 => __('Yes'),
                0 => __('No')
            )
        ));
        $fieldset->addField('enabled', 'select', array(
            'label' => __('Enabled'),
            'name' => 'enabled',
            'options' => array(
                1 => __('Yes'),
                0 => __('No')
            )
        ));
        $fieldset->addField('order', 'text', array(
            'label' => __('Sort Order'),
            'name' => 'order',
            'validate_class' => 'validate-number'
        ));
        $site = $this->_coreRegistry->registry('group');
        $form->setValues($site->getData());

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Group Details');
    }

    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }



}