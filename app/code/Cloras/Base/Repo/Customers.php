<?php

namespace Cloras\Base\Repo;

use Cloras\Base\Api\CustomerIndexRepositoryInterface;
use Cloras\Base\Api\CustomerInterface;
use Cloras\Base\Api\Data\ItemsInterfaceFactory;
use Cloras\Base\Api\Data\ResultsInterfaceFactory;
use Cloras\Base\Model\Data\CustomerDTO as Customer;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

class Customers implements CustomerInterface
{
    private $customerRepository;

    private $customerIndexRepository;

    private $searchCriteriaBuilder;

    private $itemsFactory;

    private $jsonHelper;

    private $registry;

    private $customerFactory;

    private $customerResource;

    private $clorasHelper;

    private $resultsFactory;

    private $addressInterfaceFactory;

    private $addressFactory;

    private $regionFactory;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressInterfaceFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        ItemsInterfaceFactory $itemsFactory,
        CustomerFactory $customerFactory,
        Json $jsonHelper,
        Registry $registry,
        CustomerResource $customerResource,
        CustomerIndexRepositoryInterface $customerIndexRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        ResultsInterfaceFactory $resultsFactory,
        \Cloras\Base\Helper\Data $clorasHelper,
        CustomerMetadataInterface $customerMetadata,
        AddressMetadataInterface $addressMetadata
    ) {
        $this->customerRepository      = $customerRepository;
        $this->searchCriteriaBuilder   = $searchCriteriaBuilder;
        $this->itemsFactory            = $itemsFactory;
        $this->jsonHelper              = $jsonHelper;
        $this->registry                = $registry;
        $this->customerFactory         = $customerFactory;
        $this->customerResource        = $customerResource;
        $this->customerIndexRepository = $customerIndexRepository;
        $this->_filterBuilder          = $filterBuilder;
        $this->regionFactory           = $regionFactory;
        $this->addressFactory          = $addressFactory;
        $this->_filterGroupBuilder     = $filterGroupBuilder;
        $this->_addressFactory         = $addressFactory;
        $this->clorasHelper            = $clorasHelper;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->resultsFactory          = $resultsFactory;
        $this->customerMetadata        = $customerMetadata;
        $this->addressMetadata         = $addressMetadata;
    }//end __construct()

    /**
     * @return \Cloras\Base\Api\Data\ItemsInterface
     */
    public function getCustomers()
    {
    
        $items = $this->itemsFactory->create();

        // $filterAttribute['status'] = [
        //     'Pending',
        //     'Failed',
        // ];

        $filterAttribute['status'] = [
            'Pending'
        ];

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('status', $filterAttribute['status'], 'in')->create();

        $loggedCustomers = $this->customerIndexRepository->getCustomerIds($searchCriteria);

        if ($loggedCustomers->getTotalCount()) {
            $loadedCustomers = [];
            $customersIds    = $loggedCustomers->getItems();

            $customerFilters = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $customersIds['all'], 'in')->create();
            $customers       = $this->customerRepository->getList($customerFilters)->getItems();
            
            $newCustomerCount = 0;
            $updatedCustomerCount = 0;
            $customerData = [];
            foreach ($customers as $customer) {
                $loadedCustomers[] = $customer->getId();
        //$customerData = $this->clorasHelper->customizedCustomers($customer);
                if (in_array($customer->getId(), $customersIds['new'])) {
                    $items->addNewCustomer($customer);
                    $newCustomerCount++;
                } else {
                    $items->addUpdatedCustomer($customer);
                    $updatedCustomerCount++;
                }
            }
            $items->setNewCustomerCount($newCustomerCount);
            $items->setUpdatedCustomerCount($updatedCustomerCount);
            $items->setTotalCustomers(count($customers));

            if (!empty($loadedCustomers)) {
                $customerStatus = [
                    Customer::STATUS_PENDING,
                    Customer::STATUS_FAILED,
                ];
                $this->customerIndexRepository->updateStatuses($loadedCustomers, $customerStatus, Customer::STATUS_PROCESS);
            }
        }//end if

        return $items;
    }//end getCustomers()

    /**
     * @param string $customers
     *
     * @return boolean
     */
    public function updateCustomers($customers)
    {
        try {
            $customers       = $this->jsonHelper->unserialize($customers);
            $syncedCustomers = [];
            $response        = [];
            $customerStatus  = [Customer::STATUS_PROCESS];
            foreach ($customers as $customer) {
                $response[] = $customer;
            }
          
            // Tell cloras ignore this update
            $this->registry->register('ignore_customer_update', 1);

            // Negative Performance Impact - Load & Save in loop
            foreach ($response[0] as $newCustomer) {
                $syncedCustomers[] = $newCustomer['entity_id'];
                $this->updateCustomerAttribute($newCustomer);
            }

            $customersToBeUpdated = array_merge($syncedCustomers, $response[1]);
            // Updating Customer status to completed (For new Customers)
            if (!empty($customersToBeUpdated)) {
                $this->customerIndexRepository->updateStatuses($customersToBeUpdated, $customerStatus, Customer::STATUS_COMPLETED);
            }

            // Failed Customers
            if (!empty($response[2])) {
                $this->customerIndexRepository->updateStatuses($response[2], $customerStatus, Customer::STATUS_FAILED);
            }

            return true;
        } catch (\Exception $e) {
            //$this->logger->info('Customer Update Error: ' . (array)$e->getMessage());
           
            return false;
        }//end try
    }//end updateCustomers()

    private function updateCustomerAttribute($newCustomer)
    {
        $customer = $this->customerRepository->getById($newCustomer['entity_id']);
        $customAttributes = [
            'cloras_erp_customer_id' => 'customer_id',
            'cloras_erp_contact_id' => 'contact_id',
            'cloras_erp_shipto_id' => 'shipto_id'
        ];
        foreach ($customAttributes as $attributeCode => $attributeValue) {
            if (array_key_exists($attributeValue, $newCustomer)) {
                $customer->setCustomAttribute($attributeCode, $newCustomer[$attributeValue]);
            }
        }
        $this->customerRepository->save($customer);
    }

    /**
     * @param string $data
     *
     * @return \Cloras\Base\Api\Data\ResultsInterface
     */
    public function updateCustomerDetails($data)
    {

        
        $customersInfo = $this->jsonHelper->unserialize($data);
        $response = [
            'total_count'   => 0,
            'success_count' => 0,
            'failure_count' => 0,
            'failed_ids'    => [],
        ];
        $this->registry->register('ignore_customer_update', 1);
        
        foreach ($customersInfo as $customerInfo) {
            $response['total_count'] += 1;
            $erpCustomerId            = $customerInfo['customer_id'];

            try {
                $billingAddress = !empty($customerInfo['addresses']) ? $customerInfo['addresses'][0] : null;
                $contacts = !empty($customerInfo['contacts']) ? $customerInfo['contacts'] : null;
                // print_r($contacts);
                
                if ($billingAddress) {
                    $filterByERPCustomerId = $this->searchCriteriaBuilder->addFilter('cloras_erp_customer_id', $erpCustomerId)->create();
                    $customers             = $this->customerRepository->getList($filterByERPCustomerId);

                    if ($customers->getTotalCount()) {
                        
                        $loadedCustomers = $customers->getItems();

                        foreach ($loadedCustomers as $customer) {
                            $this->updateCustomerAddresses($customer, $billingAddress, $contacts);
                        }//end foreach

                        $response['success_count'] += 1;
                    } else {

                        if ($this->createCustomer($billingAddress, $contacts)) {
                            $response['success_count'] += 1;
                        } else {
                            throw new LocalizedException(__('Unable to create customer / No Matched Customer Found'));
                        }
                        
                    }//end if
                } else {
                    throw new LocalizedException(__('No Billing Address Available to Update'));
                }//end if
            } catch (\Exception $e) {
                $response['failure_count']             += 1;
                $response['failed_ids'][$erpCustomerId] = $e->getMessage();
            }//end try
        }//end foreach
                            
        
        /*
         * @var \Cloras\Base\Api\Data\ResultsInterface
         */
        $results = $this->resultsFactory->create();
        $results->setResponse($response);

        return $results;
    }//end updateBillingAddress()

    private function getContactDetails($billingAddress, $contacts, $customerId)
    {
        
        $firstname = "";
        $lastname = "";
        
        $name = explode(' ', $billingAddress['name'], 2);
                                    
        if (array_key_exists(0, $name)) {
            $firstname = $name[0];
        }
                            
        if (array_key_exists(1, $name)) {
            $lastname = $name[1];
        }

        $customerRepo = $this->customerRepository->getById($customerId);
                           
        if (!empty($contacts)) {
            foreach ($contacts as $contact) {
                if (array_key_exists('email_address', $contact)) {
                    if ($customerRepo->getEmail() == $contact['email_address']) {
                        $firstname = $contact['first_name'];
                        $lastname = $contact['last_name'];
                    }
                }
            }
        }

        if (empty($lastname)) {
            $lastname = $firstname;
        }

        return [
            $firstname,
            $lastname
        ];
    }

    private function updateCustomerData($customerId, $firstname, $lastname)
    {

        $customerRepo = $this->customerRepository->getById($customerId);
        $customerRepo->setFirstname($firstname);
        $customerRepo->setLastname($lastname);
        $this->customerRepository->save($customerRepo);
    }

    private function updateCustomerAddresses($customer, $billingAddress, $contacts)
    {
        $addresses = $customer->getAddresses();
        
        $customerId = $customer->getId();
        list($firstname, $lastname) = $this->getContactDetails($billingAddress, $contacts, $customerId);
        
        $this->updateCustomerData($customerId, $firstname, $lastname);
        
        $billingAddress['firstname'] = $firstname;
        $billingAddress['lastname'] = $lastname;

        if (!empty($addresses)) {
            foreach ($addresses as $address) {

                if ($address->isDefaultBilling() && $address->isDefaultShipping()) {
                    $this->updateAddressData($address, $billingAddress, $customerId);
                } else {
                    if ($address->isDefaultBilling()) {
                        $billingAddressData = $address;
                        $this->updateAddressData(
                            $billingAddressData,
                            $billingAddress,
                            $customerId,
                            'bill'
                        );
                    }

                    if ($address->isDefaultShipping()) {
                        $shippingAddressData = $address;
                        $this->updateAddressData(
                            $shippingAddressData,
                            $billingAddress,
                            $customerId,
                            'ship'
                        );
                    }

                    if (!$address->isDefaultBilling() && !$address->isDefaultShipping()) {
                        $companyAddressData = $address;
                        $this->updateAddressData(
                            $companyAddressData,
                            $billingAddress,
                            $customerId,
                            'company'
                        );
                    }
                }
            }
        } else {
            $this->createCustomerAddress($billingAddress, $customerId);
        }
    }

    private function updateAddressData($addressData, $billingAddress, $customerId, $updateTo = 'both')
    {
        //$addressDetails = $this->addressFactory->create();
        $addressInfo = [];
        if ($updateTo != "bill" && $updateTo != "ship") {
            if (!empty($billingAddress['name'])) {
                $addressData->setCompany($billingAddress['name']);
            }


            if (!empty($billingAddress['firstname'])) {
                $addressData->setFirstname($billingAddress['firstname']);
            }

            if (!empty($billingAddress['lastname'])) {
                $addressData->setLastname($billingAddress['lastname']);
            }
        }
        
        $addressInfo['name'] = $billingAddress['name'];
                
        $addressInfo['firstname'] = $billingAddress['firstname'];
        
        $addressInfo['lastname'] = $billingAddress['lastname'];
        
        $addressInfo['telephone'] = $billingAddress['central_phone_number'];
        
        if ($updateTo == "bill") {

            $addressInfo['street1'] = $billingAddress['mail_address1'];
            $addressInfo['street2'] = $billingAddress['mail_address2'];
            $addressInfo['street3'] = $billingAddress['mail_address3'];
            $addressInfo['city'] = $billingAddress['mail_city'];
            $addressInfo['country'] = $billingAddress['mail_country'];
            $addressInfo['state'] = $billingAddress['mail_state'];
            $addressInfo['post_code'] = $billingAddress['mail_postal_code'];
            
        } elseif ($updateTo == "ship") {
            
            $addressInfo['street1'] = $billingAddress['phys_address1'];
            $addressInfo['street2'] = $billingAddress['phys_address2'];
            $addressInfo['street3'] = $billingAddress['phys_address3'];
            $addressInfo['city'] = $billingAddress['phys_city'];
            $addressInfo['country'] = $billingAddress['phys_country'];
            $addressInfo['state'] = $billingAddress['phys_state'];
            $addressInfo['post_code'] = $billingAddress['phys_postal_code'];

        } elseif ($updateTo == 'both') {
            
            $addressInfo['street1'] = $billingAddress['mail_address1'];
            $addressInfo['street2'] = $billingAddress['mail_address2'];
            $addressInfo['street3'] = $billingAddress['mail_address3'];
            $addressInfo['city'] = $billingAddress['mail_city'];
            $addressInfo['country'] = $billingAddress['mail_country'];
            $addressInfo['state'] = $billingAddress['mail_state'];
            $addressInfo['post_code'] = $billingAddress['mail_postal_code'];
        }

        $this->createAddressData($addressInfo, $customerId, $addressData, 1);

    
        //$addressDetails->updateData($addressData)->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerMetaData()
    {
        $attributesMetadata = $this->customerMetadata->getAllAttributesMetadata();

        return $attributesMetadata;
    }//end getCustomerMetaData()

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddressMetaData()
    {
        $attributesMetadata = $this->addressMetadata->getAllAttributesMetadata();

        return $attributesMetadata;
    }//end getCustomerAddressMetaData()

    public function createCustomer($billingAddress, $contacts)
    {
        
        try {
           
            if (!empty($billingAddress)) {
                
                if (array_key_exists('id', $billingAddress)) {
                    $erpCustomerId = $billingAddress['id'];
                
                    if (!empty($contacts)) {
                        $customerData = $contacts[0];
                        if (array_key_exists('email_address', $customerData)) {
                            if (!empty($customerData['email_address'])) {
                                
        
                        
                                $websiteId = $this->storeManagerInterface->getWebsite()->getWebsiteId();
                                $store = $this->storeManagerInterface->getStore();  // Get Store ID
                         
                                $storeId = $store->getStoreId();
                                $customers = $this->customerFactory->create();

                                $customers->setEmail($customerData['email_address']);
                                 
                                $customers->setFirstname($customerData['first_name']);
                                if (!empty($customerData['last_name'])) {
                                    $customers->setLastname($customerData['last_name']);
                                } else {
                                    $customers->setLastname($customerData['first_name']);
                                }
                                 
                                $customers->setPassword($customerData['email_address']);
                                 
                                $customers->save();

                                $customerId = $customers->getId();
                                if ($customerId) {

                                    if (array_key_exists('id', $customerData)) {
                                        $newCustomer['entity_id'] = $customerId;
                                        $newCustomer['customer_id'] = $erpCustomerId;
                                        $newCustomer['contact_id'] = $customerData['id'];
                                        $newCustomer['shipto_id'] = $erpCustomerId;
                                        $this->updateCustomerAttribute($newCustomer);
                                    }
                                    
                                    $billingAddress['firstname'] = $contacts[0]['first_name'];

                                    if (empty($contacts[0]['last_name'])) {
                                        $billingAddress['lastname'] = $contacts[0]['first_name'];
                                    } else {
                                        $billingAddress['lastname'] = $contacts[0]['last_name'];
                                    }
                                    
                                    
                                    if ($this->createCustomerAddress($billingAddress, $customerId)) {
                                        return true;
                                    } else {
                                        return false;
                                    }
                                }
                            }
                        }
                        
                    }
                }
            }
            return true;
        } catch (\Exception $e) {
            //print_r($e->getMessage());exit();
            return false;
        }
    }

    public function createCustomerAddress($billingAddress, $customerId)
    {

        try {
            $addressInfo = [];
            $addressInfo['name'] = $billingAddress['name'];
            $addressInfo['firstname'] = $billingAddress['firstname'];
            $addressInfo['lastname'] = $billingAddress['lastname'];
            $addressInfo['telephone'] = $billingAddress['central_phone_number'];
            $addressInfo['street1'] = $billingAddress['mail_address1'];
            $addressInfo['street2'] = $billingAddress['mail_address2'];
            $addressInfo['street3'] = $billingAddress['mail_address3'];
            $addressInfo['city'] = $billingAddress['mail_city'];
            $addressInfo['country'] = $billingAddress['mail_country'];
            $addressInfo['state'] = $billingAddress['mail_state'];
            $addressInfo['post_code'] = $billingAddress['mail_postal_code'];

            $this->createAddressData($addressInfo, $customerId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function createAddressData($addressInfo, $customerId, $addressData = [], $mode = 0)
    {
        
        /*default mode create*/
        if ($mode == 0) {
            //create new address
            $addressData = $this->addressFactory->create();
        } else {
            $addressDetails = $this->addressFactory->create();
        }

        if (is_object($addressData)) {
            
            $countryId = $this->getCountryname($addressInfo['country']);

            if (!empty($countryId)) {

                $addressData->setCustomerId($customerId)
                ->setCompany($addressInfo['name'])
                ->setFirstname($addressInfo['firstname'])
                ->setLastname($addressInfo['lastname']);
                $addressData->setCity($addressInfo['city'])
                    ->setCountryId($countryId);

                $mailAddress = [ $addressInfo['street1'] ];

                if ($addressInfo['street2'] != null) {
                    $mailAddress[] = $addressInfo['street2'];
                }
                if ($addressInfo['street3'] != null) {
                    $mailAddress[] = $addressInfo['street3'];
                }
                
                $addressData->setStreet($mailAddress);
                   
                if (!empty(trim($addressInfo['telephone']))) {
                    $addressData->setTelephone($addressInfo['telephone']);
                } else {
                    $addressData->setTelephone('123456789');
                }
                
                $addressData->setPostcode($addressInfo['post_code']);
                $region   = $this->regionFactory->create();
                $regionId = $region->loadByCode(
                    $addressInfo['state'],
                    $countryId
                )->getId();

                if ($regionId) {
                    $addressData->setRegionId($regionId);
                }
                

                if ($mode == 0) {
                    $addressData->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1')
                    ->setSaveInAddressBook('1');
                    
                    $addressData->save();
                } else {
                    $addressDetails->updateData($addressData)->save();
                }
            }

            
        }
    }

    public function getCountryname($countryName)
    {
        $countryId = '';
        $countryCollection = $this->country->getCollection();
        foreach ($countryCollection as $country) {
            
            if ((strtolower($countryName) == strtolower($country->getName())) || (strtolower($country->getCountryId()) == strtolower($countryName))) {
                $countryId = $country->getCountryId();
                break;
            }
        }

        return $countryId;
    }
}//end class
