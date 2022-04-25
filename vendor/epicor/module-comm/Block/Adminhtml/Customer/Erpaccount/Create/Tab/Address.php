<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Create\Tab;


class Address extends \Epicor\Common\Block\Customer\Erpaccount\Address
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Helper\Address $customerAddressHelper,
        \Magento\Directory\Model\Config\Source\CountryFactory $directoryConfigSourceCountryFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Data\Form\Element\SelectFactory $formElementSelectFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    )
    {
        $this->backendSession = $context->getBackendSession();
        parent::__construct(
            $context,
            $customerAddressHelper,
            $directoryConfigSourceCountryFactory,
            $formFactory,
            $formElementSelectFactory,
            $directoryHelper,
            $data
        );
        $this->setTemplate('Epicor_Comm::epicor_comm/customer/erpaccount/new/addresses.phtml');
    }

    protected function _prepareForm()
    {
        $form = $this->formFactory->create();
        $formData = $this->backendSession->getFormData(true);
        $data = array();

        if (isset($formData['registered'])) {
            foreach ($formData['registered'] as $key => $value) {
                $data['registered_' . $key] = $value;
            }
        } else {
            //M1 > M2 Translation Begin (Rule p2-7)
            //$data['registered_country'] = Mage::helper('core')->getDefaultCountry();
            $data['registered_country'] = $this->directoryHelper->getDefaultCountry();
            //M1 > M2 Translation End
        }

        if (isset($formData['invoice'])) {
            foreach ($formData['invoice'] as $key => $value) {
                $data['invoice_' . $key] = $value;
            }
        } else {
            //M1 > M2 Translation Begin (Rule p2-7)
            //$data['invoice_country'] = Mage::helper('core')->getDefaultCountry();
            $data['invoice_country'] = $this->directoryHelper->getDefaultCountry();
            //M1 > M2 Translation End

        }

        if (isset($formData['delivery'])) {
            foreach ($formData['delivery'] as $key => $value) {
                $data['delivery_' . $key] = $value;
            }
        } else {
            //M1 > M2 Translation Begin (Rule p2-7)
            //$data['delivery_country'] = Mage::helper('core')->getDefaultCountry();
            $data['delivery_country'] = $this->directoryHelper->getDefaultCountry();
            //M1 > M2 Translation End
        }

        $showRegisteredAddress = $this->scopeConfig->isSetFlag('epicor_b2b/registration/registered_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $showInvoiceAddress = $this->scopeConfig->isSetFlag('epicor_b2b/registration/invoice_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $showDeliveryAddress = $this->scopeConfig->isSetFlag('epicor_b2b/registration/delivery_address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $form->setValues($data);
        $this->setForm($form);
        $arrayDependencies = array();
        if ($showRegisteredAddress) {
            $this->_addFormAddress('registered', array(), false, $arrayDependencies, $this->scopeConfig->isSetFlag('epicor_b2b/registration/registered_address_phone_fax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $arrayDependencies[] = 'registered';
        }
        if ($showInvoiceAddress) {
            $this->_addFormAddress('invoice', array(), false, $arrayDependencies, $this->scopeConfig->isSetFlag('epicor_b2b/registration/invoice_address_phone_fax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $arrayDependencies[] = 'invoice';
        }
        if ($showDeliveryAddress) {
            $this->_addFormAddress('delivery', array(), false, $arrayDependencies, $this->scopeConfig->isSetFlag('epicor_b2b/registration/delivery_address_phone_fax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        }
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
