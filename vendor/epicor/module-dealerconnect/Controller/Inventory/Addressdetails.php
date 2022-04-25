<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Inventory;

use Magento\Framework\Controller\ResultFactory;

class Addressdetails extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory
     */
    protected $commCustomerErpaccountAddressFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     *
     * @var \Magento\Customer\Model\Session 
     */
    protected $customerSession;
    
    /**
     *
     * @var \Magento\Framework\App\Request\Http 
     */
    protected $request;
   
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commCustomerErpaccountAddressFactory = $commCustomerErpaccountAddressFactory;
        $this->customerSession = $customerSession;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->request =  $request;
        parent::__construct(
            $context
        );
    }

    public function execute()
    {

        $addressId = $this->request->getParam('addressid');
     
        $customer = $this->customerSession->getCustomer();

        if ($addressId) {
            if (strpos($addressId, 'erpaddress_') !== false) {
                $addressId = str_replace('erpaddress_', '', $addressId);

                $erpAddress = $this->commCustomerErpaccountAddressFactory->create()->load($addressId);

                $address = $erpAddress->toCustomerAddress($customer);
            } else {
                $address = $customer->getAddressById($addressId);
            }
            
        } else {
            $addressParam = $this->request->getParam('address-data');
            $addressData = !empty($addressParam) ? (array)json_decode($addressParam) : array();

            $address = $this->dataObjectFactory->create($addressData);
        }
        
        $result = [
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'company' => $address->getCompany(),
                'address1' => $address->getStreet()[0],
                'address2' => isset($address->getStreet()[1]) ? $address->getStreet()[1] : '',
                'address3' => isset($address->getStreet()[2]) ? $address->getStreet()[2] : '',
                'city' => $address->getCity(),
                'county' => $address->getCounty() ?: $address->getRegionCode(),
                'region' => $address->getRegion(),
                'region_id' => $address->getRegionId(),
                'country' => $address->getCountry(),
                'postcode' => $address->getPostcode(),
                'email' => $address->getEccEmail(),
                'telephone' => $address->getTelephone(),
                'fax' => $address->getFax(),
                'address_code' => $address->getEccErpAddressCode(),
                'instructions' => $address->getEccInstructions(),
                'company' => $address->getCompany(),
                ];
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }

}
