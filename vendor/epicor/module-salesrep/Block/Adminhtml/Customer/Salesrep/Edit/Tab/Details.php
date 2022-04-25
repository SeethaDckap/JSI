<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Adminhtml\Customer\Salesrep\Edit\Tab;


class Details extends \Epicor\Common\Block\Adminhtml\Form\AbstractBlock implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $this->registry = $registry;
        $this->formFactory = $formFactory;

        parent::__construct($context, $data);
    }



    public function canShowTab()
    {
        return true;
    }

    public function getTabLabel()
    {
        return 'Details';
    }

    public function getTabTitle()
    {
        return 'Details';
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $salesRep = $this->registry->registry('salesrep_account');
        /* @var $salesRep \Epicor\SalesRep\Model\Account */


        $form = $this->formFactory->create();
        $formData = $this->_backendSession->getFormData(true);

        if (empty($formData)) {
            $formData = $salesRep->getData();
        }

        $fieldset = $form->addFieldset('details', array('legend' => __('Sales Rep Account Details')));

        $fieldset->addField('sales_rep_id', 'text', array(
            'label' => __('Sales Rep Account Number'),
            'required' => true,
            'name' => 'sales_rep_id',
            'disabled' => $salesRep->isObjectNew() ? false : true
        ));

        $fieldset->addField('name', 'text', array(
            'label' => __('Name'),
            'required' => true,
            'name' => 'sales_rep_name'
        ));

        $fieldset->addField('catalog_access', 'select', array(
            'label' => __('Sales Reps Can Access Catalog'),
            'required' => false,
            'name' => 'catalog_access',
            'values' => array(
                array(
                    'label' => __('Global Default'),
                    'value' => null
                ),
                array(
                    'label' => __('Yes'),
                    'value' => 'Y'
                ),
                array(
                    'label' => __('No'),
                    'value' => 'N'
                )
            )
        ));

        $form->setValues($formData);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
