<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Customer;

/**
 * Processes a customer being renamed and updates their ERP Account addresses
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class ProcessCustomerRename implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $commHelper;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
    )
    {
        $this->customerCustomerFactory = $customerCustomerFactory;
    }
    
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerObj = $observer->getEvent()->getCustomer()->getData();
        $customer = $observer->getEvent()->getCustomer();
        

        /* @var $customerLoaded \Epicor\Comm\Model\Customer */
        $customerLoaded = $this->customerCustomerFactory->create()->load($customerObj['entity_id']);
        
        $collection = $customerLoaded->getAddressesCollection();
        
        $collection->addAttributeToFilter('ecc_erp_address_code', ['notnull' => true]);
        $collection->getSelect()->where('firstname != :firstname OR lastname != :lastname');
        $collection->addBindParam('firstname',$customer->getFirstname());
        $collection->addBindParam('lastname',$customer->getLastname());
        
        $defaultBill = ($customerLoaded->getDefaultBillingAddress()) ? $customerLoaded->getDefaultBillingAddress()->getId() : false;
        $defaultShip = ($customerLoaded->getDefaultShippingAddress()) ? $customerLoaded->getDefaultShippingAddress()->getId() : false;
        
        if ($collection->getSize() > 0) {
            $addresses = $collection->getItems();
            foreach ($addresses as $address) {
                /* @var $customerLoaded \Epicor\Comm\Model\Customer\Address */
                if ($customer->getFirstname() != $address->getFirstname()) {
                    $address->setFirstname($customer->getFirstname());
                    $save = true;
                }

                if ($customer->getLastname() != $address->getLastname()) {
                    $address->setLastname($customer->getLastname());
                    $save = true;
                }

                if ($save) {
                    // have to do this, or it will wipe the address from being the default :(
                    if ($address->getId() == $defaultBill) {
                        $address->setData('is_default_billing', 1);
                    }
                    
                    if ($address->getId() == $defaultShip) {
                        $address->setData('is_default_shipping', 1);
                    }
                        
                    $address->save();
                }
            }
        }
        
    }

}