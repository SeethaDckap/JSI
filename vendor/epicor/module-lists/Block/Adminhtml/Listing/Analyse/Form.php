<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Analyse;


class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Epicor\Comm\Model\Config\Source\Sync\StoresFactory
     */
    protected $commConfigSourceSyncStoresFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Comm\Model\Config\Source\Sync\StoresFactory $commConfigSourceSyncStoresFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->commConfigSourceSyncStoresFactory = $commConfigSourceSyncStoresFactory;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _prepareForm()
    {

        $form = $this->formFactory->create(
            [
                'data' => ['id' => 'edit_form', 'action' => $this->getUrl('*/*/analyse'), 'method' => 'post'],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('layout_block_form', array(
            'legend' => __('Analyse Lists')
        ));

        $fieldset->addType('customer', 'Epicor\Comm\Block\Adminhtml\Form\Element\Customer');
        $fieldset->addType('erpaccount', 'Epicor\Comm\Block\Adminhtml\Form\Element\Erpaccount');
        $fieldset->addType('product', 'Epicor\Comm\Block\Adminhtml\Form\Element\Product');

        $fieldset->addField('store_id', 'select', array(
            'label' => __('Store'),
            'values' => $this->commConfigSourceSyncStoresFactory->create()->toOptionArray(__('No Store Selected')),
            'name' => 'store_id'
        ));

        $fieldset->addField('customer_type', 'select', array(
            'label' => __('Customer Type'),
            'name' => 'customer_type',
            'values' => array(
                array(
                    'label' => __('No Customer Type Selected'),
                    'value' => ''
                ),
                array(
                    'label' => __('B2B'),
                    'value' => 'B'
                ),
                array(
                    'label' => __('B2C'),
                    'value' => 'C'
                )
            ),
        ));

        $fieldset->addField('ecc_erpaccount_id', 'erpaccount', array(
            'label' => __('Erp Account'),
            'name' => 'ecc_erpaccount_id',
        ));

        $fieldset->addField('customer_id', 'customer', array(
            'label' => __('Customer'),
            'name' => 'customer_id',
        ));

        $fieldset->addField('sku', 'product', array(
            'label' => __('Product'),
            'name' => 'sku',
        ));

        $form->addValues($this->getRequest()->getPost());

        return parent::_prepareForm();
    }

}
