<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Attribute\Backend;


class DefaultBackend
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
    protected $shippingdates;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\CheckoutAgreements\Model\ResourceModel\Agreement\CollectionFactory $agreementCollectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Model\Checkout\Dates $shippingdates,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\AddressFactory $customerAddressFactory
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->shippingdates = $shippingdates;
        $this->checkoutSession = $checkoutSession;
        $this->commHelper = $commHelper;
        $this->customerAddressFactory = $customerAddressFactory;
    }
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function beforeBeforeSave(
        \Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend $subject,
        \Magento\Framework\DataObject $object
    ) {
        $attrCode = $subject->getAttribute()->getAttributeCode();
        if($attrCode == 'street'){
            $value = $this->processValue($object->getData($attrCode)); 
           $attribute = $subject->getAttribute();
           $maxAllowedLineCount = $attribute->getData('multiline_count');
           if (count($value) > $maxAllowedLineCount) {
               $newvalue = [];
               for($i=0; $i<$maxAllowedLineCount; $i++){
                    $newvalue[] = $value[$i];               
               }
               if (is_array($newvalue)) {
                 $newvalue = trim(implode("\n", $newvalue));
               }
               $object->setData($attrCode, $newvalue);
           }
        }
         
    }
    /**
     * Process value before validation
     *
     * @param bool|string|array $value
     * @return array list of lines represented by given value
     */
    protected function processValue($value)
    {
        if ($value === false) {
            // try to load original value and validate it
            $attribute = $this->getAttribute();
            $entity = $this->getEntity();
            $value = $entity->getDataUsingMethod($attribute->getAttributeCode());
        }
        if (!is_array($value)) {
            $value = explode("\n", $value);
        }
        return $value;
    }
    
    
    
}