<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;


/**
 * Response CAD - Upload Customer Address Record
 * 
 * Send customer’s delivery details up to Websales
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Cad extends \Epicor\Comm\Model\Message\Upload
{

    private $_validationErrors = '';

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->directoryCountryFactory = $directoryCountryFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setConfigBase('epicor_comm_field_mapping/cad_mapping/');
        $this->setMessageType('CAD');
        $this->setLicenseType('Customer');
        $this->setMessageCategory(self::MESSAGE_CATEGORY_CUSTOMER);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->registry->register('entity_register_update_erpaddress', true, true);

    }

    /**
     * Process a request
     * 
     * @param array $requestData
     * @return 
     */
    public function processAction()
    {
        $this->erpData = $this->getRequest()->getCustomer();

        $brands = $this->erpData->getBrands();
        $brand = null;
        if (!is_null($brands))
            $brand = $brands->getBrand();

        if (is_array($brand))
            $brand = $brand[0];

        if (empty($brand) || !$brand->getCompany())
            //M1 > M2 Translation Begin (Rule p2-6.5)
            //$brand = $this->getHelper()->getStoreBranding(Mage::app()->getDefaultStoreView()->getId());
            $brand = $this->getHelper()->getStoreBranding($this->storeManager->getDefaultStoreView()->getId());
            //M1 > M2 Translation End

        $company = $brand->getCompany();

        $accountCode = $this->getVarienData('customer_account_code', $this->erpData);

        if (!empty($company)) {
            $delimiter = $this->getHelper()->getUOMSeparator();
            $this->setVarienData('customer_account_code', $this->erpData, $company . $delimiter . $accountCode);
        }
        $customer_code = $this->getVarienData('customer_account_code');
        $addresses = $this->getVarienDataArray('customer_addresses');
        $this->setMessageSubject($customer_code);

        $erp_customer = $this->getErpAccount($customer_code);
        if ($erp_customer->getId()) {
            $stores = array();
            $this->_loadStores($this->erpData, true);

            $helper = $this->commonXmlHelper->create();

            $brands = $helper->varienToArray($this->erpData->getVarienDataArrayFromPath('brands/brand'));

            foreach ($this->_stores as $store) {
                $stores[] = $store->getId();
            }

            $helper = $this->commMessagingHelper->create();
            foreach ($addresses as $x => $erp_address) {

                $addressCode = $this->getVarienData('customer_address_code', $erp_address);
                $delete = $this->getVarienDataFlag('customer_address_delete', $erp_address);

                $type = $helper->getAddressTypeMapping($this->getVarienData('customer_address_type', $erp_address));
                if ($delete) {
                    if ($this->isUpdateable('customer_address_delete_update')) {
                        if ($erp_customer->hasAddressCode($addressCode)) {
                            $address = $erp_customer->unsetType($type, $addressCode)->getAddress($addressCode);

                            if (!$address->getIsRegistered() &&
                                !$address->getIsInvoice() &&
                                !$address->getIsDelivery()) {
                                $erp_customer->removeAddress($addressCode);
                            }
                        }
                    }
                } else {
                    $address_name = $this->getVarienData('customer_address_name', $erp_address);
                    $address_line1 = $this->getVarienData('customer_address_line1', $erp_address);
                    $address_line2 = $this->getVarienData('customer_address_line2', $erp_address);
                    $address_line3 = $this->getVarienData('customer_address_line3', $erp_address);
                    $address_city = $this->getVarienData('customer_address_city', $erp_address);
                    $address_county = $this->getVarienData('customer_address_county', $erp_address);
                    $address_country = $helper->getCountryCodeMapping($this->getVarienData('customer_address_country', $erp_address), $helper::ERP_TO_MAGENTO);
                    $address_postcode = $this->getVarienData('customer_address_postcode', $erp_address);
                    $address_email = $this->getVarienData('customer_address_email', $erp_address);
                    $address_telephone = $this->getVarienData('customer_address_telephone', $erp_address);
                    $address_mobile_number = $this->getVarienData('customer_address_mobile', $erp_address);
                    $address_mobile_number = (!empty($address_mobile_number)) ? $address_mobile_number : 'N/A';
                    $address_fax = $this->getVarienData('customer_address_fax', $erp_address);
                    $address_instructions = $this->getVarienData('customer_address_instructions', $erp_address);
                    $location = $erp_address->getLocationCode();

                    
                    if (!$this->getVarienData('customer_address_country', $erp_address)) {
                         throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ADDRESS, $type, 'Country is required'), self::STATUS_INVALID_ADDRESS);
                    }
                    
                    
                    $locHelper = $this->commLocationsHelper->create();

                    $_location = $locHelper->checkAndCreateLocation($location, $company, $this->_stores);
                    $location = is_null($_location) ?  $location : $_location->getCode();
                    $helper->validateAddressName($address_name);

                    $erp_customer->addAddress($addressCode, $type)
                        ->setType($type, $addressCode)
                        ->setAddressName($address_name, $addressCode)
                        ->setAddress1($address_line1, $addressCode)
                        ->setAddress2($address_line2, $addressCode)
                        ->setAddress3($address_line3, $addressCode)
                        ->setCity($address_city, $addressCode)
                        ->setCounty($address_county, $addressCode)
                        ->setPostcode($address_postcode, $addressCode)
                        ->setCountry($address_country, $addressCode)
                        ->setEmail($address_email, $addressCode)
                        ->setPhone($address_telephone, $addressCode)
                        ->setMobileNumber($address_mobile_number, $addressCode)
                        ->setFax($address_fax, $addressCode)
                        ->setInstructions($address_instructions, $addressCode)
                        //->setAddressStores($stores, $addressCode)
                        ->setAddressBrands($brands, $addressCode)
                        ->setAddressLocationCode($location, $addressCode);

                    $errors = $erp_customer->getAddress($addressCode)->validate();

                    if ($errors !== true && !empty($errors)) {
                        throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ADDRESS, ($x + 1), implode(' ', $errors)), self::STATUS_INVALID_ADDRESS);
                    }

                    $countryModel = $this->directoryCountryFactory->create()->loadByCode($address_country);
                    
                    if (!$helper->getCountryCodeMapping($this->getVarienData('customer_address_country', $erp_address), $helper::ERP_TO_MAGENTO, FALSE)){
                        throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ADDRESS, ($x + 1), 'Invalid Country'), self::STATUS_INVALID_ADDRESS);
                    }
                    
                    if ($countryModel->getId() != $address_country) {
                        $erp_customer->setCountry($countryModel->getId(), $addressCode);
                    }
                    if (!empty($address_county)) {
                        $collection = $this->directoryRegionFactory->create()->getResourceCollection()
                            ->addCountryFilter($countryModel->getId())
                            ->load();

                        // Check to see if the country has regions, and check if it's valid
                        if ($collection->count() > 0) {
                            // try loading a region with the county field as the code
                            $region = $this->directoryRegionFactory->create()->loadByCode($address_county, $countryModel->getId());

                            if (!empty($region) && !$region->isObjectNew()) {
                                $erp_customer->setCounty($region->getName(), $addressCode)
                                    ->setCountyCode($region->getCode(), $addressCode);
                            } else {
                                // try loading a region with the county field as the name
                                $region = $this->directoryRegionFactory->create()->loadByName($address_county, $countryModel->getId());

                                if (!empty($region) && !$region->isObjectNew()) {
                                    $erp_customer->setCounty($region->getName(), $addressCode)
                                        ->setCountyCode($region->getCode(), $addressCode);
                                } else {
                                    throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ADDRESS, ($x + 1), 'Invalid County'), self::STATUS_INVALID_ADDRESS);
                                }
                            }
                        }
                    } else {
                        $requiredStates = explode(',', $this->scopeConfig->getValue('general/region/state_required', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                        if (in_array($countryModel->getId(), $requiredStates)) {
                            throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_ADDRESS, $type, 'County is required'), self::STATUS_INVALID_ADDRESS);
                        }
                    }
                }
            }

            $erp_customer->save();
        } else {
            throw new \Exception($this->getErrorDescription(self::STATUS_CUSTOMER_NOT_ON_FILE, $this->getHelper()->removeDelimiter($customer_code)), self::STATUS_CUSTOMER_NOT_ON_FILE);
        }
    }

}
