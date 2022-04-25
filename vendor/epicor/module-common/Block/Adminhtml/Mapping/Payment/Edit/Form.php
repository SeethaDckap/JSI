<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Payment\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{



    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\PaymentmethodsFactory
     */
    protected $commErpMappingPaymentmethodsFactory;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\PaymentFactory
     */
    protected $commErpMappingPaymentFactory;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\GortriggersFactory
     */
    protected $commErpMappingGortriggersFactory;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\YesnonullFactory
     */
    protected $commErpMappingYesnonullFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Erp\Mapping\PaymentmethodsFactory $commErpMappingPaymentmethodsFactory,
        \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPaymentFactory,
        \Epicor\Comm\Model\Erp\Mapping\GortriggersFactory $commErpMappingGortriggersFactory,
        \Epicor\Comm\Model\Erp\Mapping\YesnonullFactory $commErpMappingYesnonullFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->commErpMappingPaymentmethodsFactory = $commErpMappingPaymentmethodsFactory;
        $this->commErpMappingPaymentFactory = $commErpMappingPaymentFactory;
        $this->commErpMappingGortriggersFactory = $commErpMappingGortriggersFactory;
        $this->commErpMappingYesnonullFactory = $commErpMappingYesnonullFactory;
        parent::__construct($context, $data);
    }
    protected function _prepareForm()
    {
        if ($this->_session->getPaymentMappingData()) {
            $data = $this->_session->getPaymentMappingData();
            $this->_session->getPaymentMappingData(null);
        } elseif ($this->registry->registry('payment_mapping_data')) {
            $data = $this->registry->registry('payment_mapping_data')->getData();
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

        $fieldset->addField('erp_code', 'text', array(
            'label' => __('Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'erp_code',
            'note' => __('Order Status Code'),
        ));




        $fieldset->addField('magento_code', 'select', array(
            'label' => __('Payment Method'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'magento_code',
            'values' => $this->commErpMappingPaymentmethodsFactory->create()->toOptionArray(),
            'note' => __('Order Status Description'),
        ));

        $fieldset->addField('payment_collected', 'select', array(
            'label' => __('Payment Collected'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'payment_collected',
            'values' => $this->commErpMappingPaymentFactory->create()->toOptionArray(),
            'note' => __('Is payment collected by this payment method'),
        ));

        $fieldset->addField('gor_trigger', 'select', array(
            'label' => __('Order Trigger'),
            'required' => true,
            'name' => 'gor_trigger',
            'values' => $this->commErpMappingGortriggersFactory->create()->toOptionArray(),
            'note' => __('GOR only sent if condition is true'),
        ));

        $fieldset->addField('gor_online_prevent_repricing', 'select', array(
            'label' => __('GOR Online'),
            'required' => false,
            'name' => 'gor_online_prevent_repricing',
            'values' => $this->commErpMappingYesnonullFactory->create()->toOptionArray(),
            'note' => __('Prevent Repricing for GOR for this Payment Method when order placed online?'),
        ));
        $fieldset->addField('gor_offline_prevent_repricing', 'select', array(
            'label' => __('GOR Offline'),
            'required' => false,
            'name' => 'gor_offline_prevent_repricing',
            'values' => $this->commErpMappingYesnonullFactory->create()->toOptionArray(),
            'note' => __('Prevent Repricing for GOR for this Payment Method when order placed offline?'),
        ));

        $fieldset->addField('bsv_online_prevent_repricing', 'select', array(
            'label' => __('BSV Online'),
            'required' => false,
            'name' => 'bsv_online_prevent_repricing',
            'values' => $this->commErpMappingYesnonullFactory->create()->toOptionArray(),
            'note' => __('Prevent Repricing for BSV for this Payment Method when order placed online?'),
        ));
        $fieldset->addField('bsv_offline_prevent_repricing', 'select', array(
            'label' => __('BSV Offline'),
            'required' => false,
            'name' => 'bsv_offline_prevent_repricing',
            'values' => $this->commErpMappingYesnonullFactory->create()->toOptionArray(),
            'note' => __('Prevent Repricing for BSV for this Payment Method when order placed offline?'),
        ));

        $data = $this->includeStoreIdElement($data);

        $form->setValues($data);

        return parent::_prepareForm();
    }

}
