<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Plugin\Checkout;


class LayoutProcessor
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $customerAddressFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $contactModel;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $agreementCollectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\SalesRep\Model\Checkout\Contact $contact,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->contactModel = $contact;
        $this->checkoutSession = $checkoutSession;
        $this->commHelper = $commHelper;
        $this->customerAddressFactory = $customerAddressFactory;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {
        
        if($this->contactModel->isSalesRep()){
            
            $availableContacts=[];            
            if($this->contactModel->isShow()){
                $availableContacts = $this->contactModel->getContacts();
            }
            $contactsOptions= [];
            $required = false;
            if ($this->contactModel->isRequired()) :
            $required = true;
                $contactsOptions[] = ['value'=>"",'label'=>__('Please Choose a Recipient for this Order')];
            else:
                $contactsOptions[] = ['value'=>"",'label'=>__('N/A')];
            endif; 
            $selectedEmail = false;
            if($this->checkoutSession->getQuote()->getEccSalesrepChosenCustomerInfo()){
                $selectedsalesrep = unserialize($this->checkoutSession->getQuote()->getEccSalesrepChosenCustomerInfo());
                $selectedEmail = $selectedsalesrep['email'];
            }
            $selectedsalerep = false;
            $selectedsalerepval = false;
            foreach($availableContacts as $id => $contactData){
                $label = $contactData->getName().' ('.$contactData->getEmail().')';
                $contactsOptions[] = ['value'=>$contactData->getBasedata(),'label'=>$label];
                if($contactData->getEmail() ==  $selectedEmail){
                    $selectedsalerep = $label;
                    $selectedsalerepval = $contactData->getBasedata();
                }
            }    
            $jsLayout['components']['checkout']['children']['steps']['children']['contact-step']['children']['contact-choice']
                 =[
                     'component' => 'Epicor_SalesRep/epicor/salesrep/js/view/checkout/shipping/contact',
                     'sortOrder'=>'1',
                     'selected' =>$selectedsalerep,
                     'iscontactShow' =>$this->contactModel->isShow(),
                     'validation' =>$required,
                     'children'=>[
                                 'shipping_dates' => [
                                    'component' => 'Epicor_SalesRep/epicor/salesrep/js/view/checkout/shipping/select',
                                    'config' => [
                                        'customScope' => 'shippingAddress_contact',
                                        'template' => 'ui/form/field',
                                        'elementTmpl' => 'Epicor_SalesRep/checkout/shipping/select',
                                        'options' => $contactsOptions,
                                        'value' =>$selectedsalerep,
                                        'selected' =>$selectedsalerepval,
                                        'label' => 'Contact',
                                        'id' => 'salesrep_contact'
                                    ],                                    
                                    'validation' => [
                                        'required-entry' => $required
                                    ],
                                     'selectedVal' =>$selectedsalerep,
                                    'dataScope' => 'shippingAddress_contact.salesrep_contact',
                                    'label' => 'Contact',
                                    'provider' => 'checkoutProvider',
                                    'visible' => true,
                                    'sortOrder' => 250,
                                    'id' => 'salesrep_contact'
                                ]
                            ]
                     
                  ];
        }
        
        return $jsLayout;
    }
    
}