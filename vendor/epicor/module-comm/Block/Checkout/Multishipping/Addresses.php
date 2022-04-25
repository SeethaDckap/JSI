<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Checkout\Multishipping;

use Magento\Customer\Model\Address\Config as AddressConfig;

/**
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Addresses extends \Magento\Multishipping\Block\Checkout\Addresses
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        AddressConfig $addressConfig,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        array $data = [],
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Data\Form\FormKey $formKey
        
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $filterGridFactory, $multishipping, $customerRepository, $addressConfig, $addressMapper, $data);
        $this->formKey = $formKey;
        $this->commonHelper = $commonHelper;
    }
    
    public function restrictAddressTypes()
    {
        return $this->scopeConfig->isSetFlag('Epicor_Comm/address/force_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve options for addresses dropdown
     *
     * @return array
     */
    public function getAddressOptions()
    {
        $options = $this->getData('address_options');
        if (is_null($options)) {
            $options = array();
            $customer = $this->getCheckout()->getCustomerSession()->getCustomer();
            $addresses = ($this->restrictAddressTypes()) ? $customer->getAddressesByType('delivery') : $customer->getCustomAddresses();

            foreach ($addresses as $address) {
                $options[] = array(
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                );
            }
            $this->setData('address_options', $options);
        }

        return $options;
    }

    public function canAddNew()
    {
        $helper = $this->commonHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        return $helper->customerAddressPermissionCheck('create');
    }

    //M1 > M2 Translation Begin (Rule p2-5.1)
    public function getFormKey()
    {
        return $this->formKey;
    }
    //M1 > M2 Translation End

}
