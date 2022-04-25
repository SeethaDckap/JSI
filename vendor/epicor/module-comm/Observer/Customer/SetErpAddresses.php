<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Customer;

/**
 * Sets a Customers Addresses based on their ERP Account
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class SetErpAddresses implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Epicor\Comm\Model\Import\Address
     */
    protected $_importCustomerAddress;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    private $customerRegistry;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\CustomerFactory $commMessagingCustomerHelper,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerModelFactory,
        \Epicor\Comm\Model\Import\Address $importCustomerAddress,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry
    )
    {
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
        $this->addressRepository = $addressRepository;
        $this->registry = $registry;
        $this->customerModelFactory = $customerModelFactory;
        $this->_importCustomerAddress = $importCustomerAddress;
        $this->customerRegistry = $customerRegistry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (
            $this->registry->registry('updating_erp_address')
        ) {
            return $this;
        }
        
        $this->registry->register('updating_erp_address', true);

        $customerObj = $observer->getEvent()->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */
        
        $customer = $this->customerModelFactory->create()->load($customerObj->getId());
		
		if('salesrep' === $customer->getEccErpAccountType()) {
            foreach ($customer->getAddresses() as $addrs) {
                $this->addressRepository->deleteById($addrs->getId());
            }

            return $this;
        }

        /* @var $commHelper \Epicor\Comm\Helper\Messaging\Customer */
        $commHelper = $this->commMessagingCustomerHelper->create();
        $customer_erp_address = $commHelper->getCustomerAddresses($customer);
        $erpaccountId = !empty($customer->getData('ecc_erpaccount_id')) ? $customer->getData('ecc_erpaccount_id') : $customer->getEccErpaccountId();
        $erp_group_addresses = $commHelper->getErpAddresses($customer, null, $erpaccountId);
        $erpCount = $customer->getErpAcctCounts();
        $addresses_to_add = array_diff_key($erp_group_addresses, $customer_erp_address);
        
		if(empty($erpCount) || !empty($erpCount) && count($erpCount) == 1){
            $addresses_to_delete = array_diff_key($customer_erp_address, $erp_group_addresses);
            if (count($addresses_to_delete) > 0) {
                $addressList = array();
                foreach ($addresses_to_delete as $address) {
                    /* @var $address \Epicor\Comm\Model\Customer\Address */
                    $addressList[] = $address->getId();
                }
                if (!empty($addressList)) {
                    $this->_importCustomerAddress->importCustomerAddressData($addressList, 'delete');
                }
            }
        }

        if (count($addresses_to_add) > 0) {
            $addressList = array();
            foreach ($addresses_to_add as $address) {
                /* @var $address \Epicor\Comm\Model\Customer\Erpaccount\Address */
                $cus_address = $address->toCustomerAddress($customer);
                $cus_address->setWebsiteId($customer->getWebsiteId());
                $addressList[] = $cus_address->getData();
            }
            if (!empty($addressList)) {
                $this->_importCustomerAddress->importCustomerAddressData($addressList, 'insert');
                $this->customerRegistry->remove($customer->getId());
            }
        }

        return $this;
    }

}
