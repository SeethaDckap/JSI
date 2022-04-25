<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer\Customer;

/**
 * Delete all customer addresses when Guest to B2B conversion
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class DeleteCusAddresses implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\Comm\Helper\Messaging\CustomerFactory
     */
    protected $commMessagingCustomerHelper;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModelFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    protected $customerResourceModelAddressCollectionFactory;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\CustomerFactory $commMessagingCustomerHelper,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $customerResourceModelAddressCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerModelFactory
    )
    {
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
        $this->addressRepository = $addressRepository;
        $this->registry = $registry;
        $this->customerModelFactory = $customerModelFactory;
        $this->customerResourceModelAddressCollectionFactory = $customerResourceModelAddressCollectionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getData('customer');
        $erpAccountId = $observer->getData('erp_account_Id');
        $commHelper = $this->commMessagingCustomerHelper->create();
        if($erpAccountId){
            $erp_group_addresses = $commHelper->getErpAddresses($customer, null, $erpAccountId);
            $customer_erp_address = $commHelper->getCustomerAddresses($customer);
            $addresses_to_delete = array_intersect_key($customer_erp_address, $erp_group_addresses);
            foreach ($addresses_to_delete as $address) {
                /* @var $address \Epicor\Comm\Model\Customer\Address */
                $this->addressRepository->deleteById($address->getId());
            }
        }else{
            $collection = $this->customerResourceModelAddressCollectionFactory->create();
            /* @var $collection \Magento\Customer\Model\ResourceModel\Address\Collection */
            $collection->addAttributeToFilter('parent_id', $customer->getId());

            foreach ($collection->getItems() as $address) {
                //Delete all customer addresses when Guest to B2B conversion
                $this->addressRepository->deleteById($address->getId());
            }
            return $this;
        }
    }

}
