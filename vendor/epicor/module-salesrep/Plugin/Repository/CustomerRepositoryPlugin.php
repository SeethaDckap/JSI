<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Plugin\Repository;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Api\ImageProcessorInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
/**
 * Description of CustomerRepositoryPlugin
 *
 * 
 */
class CustomerRepositoryPlugin {
     /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var \Magento\Customer\Model\ResourceModel\AddressRepository
     */
  //  protected $addressRepository;

  
    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $customerMetadata;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var ImageProcessorInterface
     */
    protected $imageProcessor;
    
   /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;
     
     public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        //\Magento\Customer\Model\ResourceModel\AddressRepository $addressRepository,
        \Magento\Customer\Api\CustomerMetadataInterface $customerMetadata,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        ImageProcessorInterface $imageProcessor,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory     
    ) {
         
        $this->customerFactory = $customerFactory;
        $this->customerRegistry = $customerRegistry;
       // $this->addressRepository = $addressRepository;
        $this->customerMetadata = $customerMetadata;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->imageProcessor = $imageProcessor;
        $this->customerSessionFactory = $customerSessionFactory;
         
    }
    
     /**
     * Plugin around customer repository save. If SalesRep is Masquerading then we do not need to Save Customer address for Sales customer account.
     *
     * @param CustomerRepository $subject
     * @param \Closure $proceed
     * @param CustomerInterface $customer
     * @param null $passwordHash
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    
    public function aroundSave(
            CustomerRepository $subject,
            \Closure $proceed,
            \Magento\Customer\Api\Data\CustomerInterface $customer,
            $passwordHash = null)
    {
        
        if(!$this->isMasquerading()){
            /** @var CustomerInterface $savedCustomer */
            return $proceed($customer, $passwordHash);
           
        }else{
                    $prevCustomerData = null;
                    if ($customer->getId()) {
                        $prevCustomerData = $this->getById($customer->getId());
                    }
                    $customer = $this->imageProcessor->save(
                        $customer,
                        CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                        $prevCustomerData
                    );

                    $origAddresses = $customer->getAddresses();
                    $customer->setAddresses([]);
                    $customerData = $this->extensibleDataObjectConverter->toNestedArray(
                        $customer,
                        [],
                        '\Magento\Customer\Api\Data\CustomerInterface'
                    );

                    $customer->setAddresses($origAddresses);
                    $customerModel = $this->customerFactory->create(['data' => $customerData]);
                    $storeId = $customerModel->getStoreId();
                    if ($storeId === null) {
                        $customerModel->setStoreId($this->storeManager->getStore()->getId());
                    }
                    $customerModel->setId($customer->getId());

                    // Need to use attribute set or future updates can cause data loss
                    if (!$customerModel->getAttributeSetId()) {
                        $customerModel->setAttributeSetId(
                            \Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER
                        );
                    }
                    // Populate model with secure data
                    if ($customer->getId()) {
                        $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
                        $customerModel->setRpToken($customerSecure->getRpToken());
                        $customerModel->setRpTokenCreatedAt($customerSecure->getRpTokenCreatedAt());
                        $customerModel->setPasswordHash($customerSecure->getPasswordHash());
                        $customerModel->setFailuresNum($customerSecure->getFailuresNum());
                        $customerModel->setFirstFailure($customerSecure->getFirstFailure());
                        $customerModel->setLockExpires($customerSecure->getLockExpires());
                    } else {
                        if ($passwordHash) {
                            $customerModel->setPasswordHash($passwordHash);
                        }
                    }

                    // If customer email was changed, reset RpToken info
                    if ($prevCustomerData
                        && $prevCustomerData->getEmail() !== $customerModel->getEmail()
                    ) {
                        $customerModel->setRpToken(null);
                        $customerModel->setRpTokenCreatedAt(null);
                    }

                    $customerModel->save();
                    $this->customerRegistry->push($customerModel);
                    $customerId = $customerModel->getId();

                  /* do not run commented code If  SalesRep is on Masquerdade mode */
                    /* 
                     if ($customer->getAddresses() !== null) {
                         if ($customer->getId()) {
                             $existingAddresses = $this->getById($customer->getId())->getAddresses();
                             $getIdFunc = function ($address) {
                                 return $address->getId();
                             };
                             $existingAddressIds = array_map($getIdFunc, $existingAddresses);
                         } else {
                             $existingAddressIds = [];
                         }

                         $savedAddressIds = [];

                         foreach ($customer->getAddresses() as $address) {
                             $address->setCustomerId($customerId)
                                 ->setRegion($address->getRegion());
                             $this->addressRepository->save($address);
                             if ($address->getId()) {
                                 $savedAddressIds[] = $address->getId();
                             }
                         } 

                         $addressIdsToDelete = array_diff($existingAddressIds, $savedAddressIds);
                         foreach ($addressIdsToDelete as $addressId) {
                             $this->addressRepository->deleteById($addressId);
                         } 
                     }
                  */
           
                    $savedCustomer = $this->get($customer->getEmail(), $customer->getWebsiteId());
                    $this->eventManager->dispatch(
                        'customer_save_after_data_object',
                        ['customer_data_object' => $savedCustomer, 'orig_customer_data_object' => $customer, 'delegate_data'=>[]]
                    );
                    
             return $savedCustomer;
        }
        
        
    }
    
    public function get($email, $websiteId = null)
    {
        $customerModel = $this->customerRegistry->retrieveByEmail($email, $websiteId);
        return $customerModel->getDataModel();
    } 
    
   public function getById($customerId)
    {
        $customerModel = $this->customerRegistry->retrieve($customerId);
        return $customerModel->getDataModel();
    }

    public function isMasquerading()
    {
        $customerSession = $this->customerSessionFactory->create();
        /* @var $customerSession \Magento\Customer\Model\Session */
        $masquerade = $customerSession->getMasqueradeAccountId();

        return !empty($masquerade);
    }
    
    
}
