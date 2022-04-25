<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Customer\Address;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

class AddressFieldsetProvider
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    /**/private $customerFactory;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->customerFactory = $customerFactory;
    }

    public function afterIsComponentVisible(
        \Magento\Customer\Ui\Component\Form\AddressFieldset $subject,
        $result
    )
    {
        if ($result) {
            $context = $subject->getContext();
            $customerId = $context->getRequestParam('id');
            $customer = $this->customerFactory->create()->load($customerId);
            $erpAccounts = $customer->getErpAcctCounts();
            if (count($erpAccounts) > 1) {
                $billingComponent = $subject->getComponent('customer_default_billing_address_wrapper');
                $billingChildComponent = $billingComponent->getComponent('customer_default_billing_address_content');
                $billingConfiguration = $billingChildComponent->getConfiguration();
                $billingConfiguration['notExistsMessage'] = __('Please check ERPs Default Billing Address');
                $billingChildComponent->setData(['config' => $billingConfiguration]);

                $shippingComponent = $subject->getComponent('customer_default_shipping_address_wrapper');
                $shippingChildComponent = $shippingComponent->getComponent('customer_default_shipping_address_content');
                $shippingConfiguration = $shippingChildComponent->getConfiguration();
                $shippingConfiguration['notExistsMessage'] = __('Please check ERPs Default Shipping Address');
                $shippingChildComponent->setData(['config' => $shippingConfiguration]);
            }
        }
        return $result;
    }
}