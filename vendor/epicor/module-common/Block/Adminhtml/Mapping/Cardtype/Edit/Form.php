<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Cardtype\Edit;


class Form extends \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Payment\Model\Config $paymentConfig,
        array $data = [])
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->registry = $registry;
        $this->formFactory = $formFactory;
        $this->paymentConfig=$paymentConfig;
        parent::__construct($context, $data);
    }

    protected function _prepareForm()
    {
        if ($this->_session->getCardtypeMappingData()) {
            $data = $this->_session->getCardtypeMappingData();
            $this->_session->getCardtypeMappingData(null);
        } elseif ($this->registry->registry('cardtype_mapping_data')) {
            $data = $this->registry->registry('cardtype_mapping_data')->getData();
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
            'payment_method', 'select', array(
            'label' => __('Payment Method'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'payment_method',
            'values' => $this->_getPaymentMethods(),
            )
        );

        $fieldset->addField(
            'magento_code', 'text', array(
            'label' => __('Card Type Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'magento_code',
            )
        );
        $fieldset->addField(
            'erp_code', 'text', array(
            'label' => __('ERP Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'erp_code',
            )
        );

        $data = $this->includeStoreIdElement($data);

        $form->setValues($data);

        return parent::_prepareForm();
    }

    public function _getPaymentMethods()
    {
        $payments = $this->paymentConfig->getActiveMethods();

        $methods = array(array('value' => '', 'label' => __('--Please Select--')));

        $methods['all'] = array(
            'label' => 'All Payment Methods',
            'value' => 'all',
        );
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->scopeConfig->getValue('payment/' . $paymentCode . '/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode,
            );
        }

        return $methods;
    }

}
