<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Message\Upload;


/**
 * Response CURP - Upload Restricted Purchase
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Curp extends \Epicor\Lists\Model\Message\Upload\AbstractModel
{

    protected $_listType = 'Restricted Purchases';

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    protected $_products;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * Curp constructor.
     * @param \Epicor\Comm\Model\Context $context
     * @param \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper
     * @param \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory
     * @param \Epicor\Lists\Model\ListModelFactory $listsListModelFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param \Epicor\Lists\Helper\Data $listsHelper
     * @param \Magento\Directory\Model\CountryFactory $directoryCountryFactory
     * @param \Magento\Directory\Model\RegionFactory $directoryRegionFactory
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Lists\Helper\Messaging\Customer $listsMessagingCustomerHelper,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        $this->dataObjectFactory = $dataObjectFactory;
        $this->listsHelper = $listsHelper;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->directoryCountryFactory = $directoryCountryFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->backendSession = $backendSession;
        $this->registry = $context->getRegistry();
        $this->_messageManager = $messageManager;

        parent::__construct(
            $context,
            $listsMessagingCustomerHelper,
            $commResourceCustomerErpaccountCollectionFactory,
            $listsListModelFactory,
            $resource,
            $resourceCollection,
            $catalogResourceModelProductCollectionFactory,
            $data
        );

        $this->setConfigBase('epicor_comm_field_mapping/curp_mapping/');
        $this->setMessageType('CURP');
    }

    public function processList()
    {
        parent::processList();
        if (!$this->_abandonUpload) {
            $this->processAddresses();
        }

        return $this;
    }

    /**
     * Process List Info & saves them against the list
     *
     * @return void
     */
    protected function processListDetails()
    {
        $list = $this->getList();
        /* @var $list Epicor_Lists_Model_ListModel */
        $erpData = $this->getErpData();
        $exists = $this->listExists();
        if (!$exists) {
            $list->setErpCode($erpData->getListCode());
            $list->setType('Rp');
            $list->setSource('erp');
            $list->setErpAccountLinkType('E');
        }
        $active = 0;
        if ($erpData->getListStatus() == "A") {
            $active = 1;
        }
        $list->setActive($active);
        if ($this->isUpdateable('settings_update', $exists, 'settings')) {
            $accountAttributes = $this->getAccountAttributes();
            $accountExclude = $accountAttributes ? $accountAttributes->getExclude() : 'N';
            //does this need another updatable check?
            $list->setErpAccountsExclusion($accountExclude);

            $settings = [$erpData->getListSettings()];
            $attributes = $erpData->getProducts()->getData('_attributes');
            if ($attributes && $attributes->getExclude() === 'Y') {
                $settings[] = 'E';
            }
            $list->setSettings($settings);
        }
        if ($this->isUpdateable('title_update', $exists, 'title')) {
            $list->setTitle($erpData->getListTitle());
        }
    }

    /**
     * Process Addresses & saves them against the list
     *
     * @return void
     */
    protected function processAddresses()
    {

        $list = $this->getList();
        $messagingHelper = $this->commMessagingHelper->create();
        $helper = $this->listsHelper;
        //$restrictedPurchase = $this->listsListModelRestrictedpurchaseFactory->create();
        $erpRestrictions = $this->_getGroupedData('restrictions', 'restriction', $this->getErpData());
        if (!is_array($erpRestrictions)) {
            $erpRestrictions = array($erpRestrictions);
        }
        $listcode = $this->listsListModelFactory->create()->load($this->getErpData()->getListCode(), 'erp_code');
        foreach ($erpRestrictions as $erpRestriction) {
            $restrictionType = $erpRestriction->getData('_attributes')->getType();
            if (!$this->_abandonUpload) {
                //$list->addRestrictions($restrictionType);
            }
            $this->registry->register('restrictionType', $restrictionType);
            switch ($restrictionType) {
                case \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_ADDRESS:
                    $allAddresses = $erpRestriction->getData('addresses');
                    $addresses = $allAddresses->getData('address');
                    $addressCodes = array();
                    if (!is_array($addresses)) {
                        $addresses = array($addresses);
                    }
                    foreach ($addresses as $address) {
                        $deleteType = $address->getData('_attributes')->getData('delete');
                        $addressData = $address->getData();
                        $addressCodes = array();
                        if (in_array($addressData['address_code'], $addressCodes)) {
                            $this->_returnMessages[] = " Repeated Address Code: {$addressData['address_code']} in CURP - Unable to continue processing. ";
                            $this->_abandonUpload = true;
                        }
                        $addressCodes[$addressData['address_code']] = $addressData['address_code'];

                        unset($addressData['_attributes']);
                        //echo '<pre>!!';print_r($addressData);
                        $country = $messagingHelper->getCountryCodeMapping($addressData['country'], 'e2m');
                        $countryModel = $this->directoryCountryFactory->create()->loadByCode($country);
                        $collection = $this->directoryRegionFactory->create()->getResourceCollection()
                            ->addCountryFilter($countryModel->getId())
                            ->load();
                        if ($countryModel->getId() && $collection->count() > 0) {
                            $region = $this->directoryRegionFactory->create()->loadByCode($addressData['county'], $countryModel->getId());
                            $regionCode = $region->getCode();
                            if (!empty($regionCode)) {
                                $addressObject = $this->dataObjectFactory->create();
                                $addressObject->setData('country', $countryModel->getId());
                                $addressObject->setData('county', $region->getCode());
                                $addressObject->setData('address1', $addressData['address1']);
                                $addressObject->setData('address2', $addressData['address2']);
                                $addressObject->setData('address3', $addressData['address3']);
                                $addressObject->setData('name', $addressData['name']);
                                $addressObject->setData('city', $addressData['city']);
                                $addressObject->setData('address_code', $addressData['address_code']);
                                $addressObject->setData('postcode', $addressData['postcode']);
                                // $finalAddress[] = $addressObject;
                                if (!$this->_abandonUpload) {
                                    if ($deleteType == 'N') {
                                        $list->addAddresses($addressObject);
                                    } elseif ($deleteType == 'Y') {
                                        $list->removeAddresses($addressObject);
                                    }
                                }
                            }
                        }
                    }
                    break;
                case \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_COUNTRY:
                    $allCountries = $erpRestriction->getData('countries');
                    $countries = $allCountries->getData('country');
                    if (!is_array($countries)) {
                        $countries = array($countries);
                    }
                    $finalCountry = array();
                    foreach ($countries as $country) {
                        $country = $messagingHelper->getCountryCodeMapping($country, 'e2m');
                        $countryModel = $this->directoryCountryFactory->create()->loadByCode($country);
                        $id = $countryModel->getId();
                        if (!empty($id)) {
                            if (!$listcode->getId() || (($helper->checkDuplicateCountry($listcode->getId(), $countryModel->getId()) == 0))) {
                                $countryObject = $this->dataObjectFactory->create();
                                $countryObject->setData('country', $countryModel->getId());
                                $finalCountry[$countryModel->getId()] = $countryObject;
                            } else {
                                $this->_logger->warning("Country $country already exist under country restriction for this list");
                                $this->_messageManager->addWarning("Country $country already exist under country restriction for this list");
                                continue;
                            }
                        } else {
                            $error = 'Invalid country ' . $country;
                            throw new \Exception($error, self::STATUS_INVALID_ADDRESS);
                        }
                    }
                    //  echo '<pre>##';print_r($finalCountry);
                    $list->addCountries($finalCountry);
                    break;
                case \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_STATE:
                    $allCounties = $this->_getGroupedData('counties', 'address', $erpRestriction);
                    if (!is_array($allCounties)) {
                        $allCounties = array($allCounties);
                    }

                    $finalCounty = array();
                    foreach ($allCounties as $key => $value) {
                        $country = $value->getData('country');
                        $county = $value->getData('county');
                        $countryCode = $messagingHelper->getCountryCodeMapping($country, 'e2m');
                        $countryModel = $this->directoryCountryFactory->create()->loadByCode($countryCode);
                        $collection = $this->directoryRegionFactory->create()->getResourceCollection()
                            ->addCountryFilter($countryModel->getId())
                            ->load();

                        $id = $countryModel->getId();
                        if ($id && $collection->count() > 0) {
                            $region = $this->directoryRegionFactory->create()->loadByCode($county,
                                $countryModel->getId());
                            $regionCode = $region->getCode();
                            if (!empty($regionCode)) {
                                if (!$listcode->getId() || (($helper->checkDuplicateCounty($listcode->getId(), $countryModel->getId(), $region->getCode()) == 0))) {
                                    $countyObject = $this->dataObjectFactory->create();
                                    $countyObject->setData('country', $countryModel->getId());
                                    $countyObject->setData('county', $region->getCode());
                                    $uniqueKey = $countryModel->getId() . '-' . $region->getCode();
                                    $finalCounty[$uniqueKey] = $countyObject;
                                } else {
                                    $this->_logger->warning("County $county already exist under country restriction for this list");
                                    $this->_messageManager->addWarning("County $county already exist under country restriction for this list");
                                    continue;
                                }
                            }
                        } elseif (empty($id)) {
                            $error = 'Invalid country ' . $country . ' in County section';
                            throw new \Exception($error, self::STATUS_INVALID_ADDRESS);
                        }
                    }

                    $list->addCounties($finalCounty);
                    break;
                case \Epicor\Lists\Model\ListModel\Address\Restriction::TYPE_ZIP:
                    $allPostcodes = $erpRestriction->getData('postcodes');
                    $postcodes = $allPostcodes->getData();

                    if (!is_array($postcodes)) {
                        $postcodes = array($postcodes);
                    }
                    $finalPostcode = array();
                    foreach ($postcodes['address'] as $key => $value) {
                        $countryCode = $messagingHelper->getCountryCodeMapping($value['country'], 'e2m');
                        $countryModel = $this->directoryCountryFactory->create()->loadByCode($countryCode);
                        $id = $countryModel->getId();
                        if ($id) {
                            $postcode = $helper->formatPostcode($value['postcode']);
                            if (!$listcode->getId() || (($helper->checkDuplicatePostcode($listcode->getId(), $countryModel->getId(), $postcode) == 0))) {
                                $postcodeObject = $this->dataObjectFactory->create();
                                $postcodeObject->setData('country', $countryModel->getId());
                                $postcodeObject->setData('postcode', $postcode);
                                $uniqueKey = $countryModel->getId() . '-' . $postcode;
                                $finalPostcode[$uniqueKey] = $postcodeObject;
                            } else {
                                $this->_logger->warning("postcodes " . $postcode . " already exist under zipcode restriction for this list");
                                $this->_messageManager->addWarning("postcodes " . $postcode . " already exist under zipcode restriction for this list");
                                continue;
                            }
                        } elseif (empty($id)) {
                            $error = 'Invalid country ' . $value['country'] . ' in Zipcode section';
                            throw new \Exception($error, self::STATUS_INVALID_ADDRESS);
                        }
                    }

                    $list->addPostcodes($finalPostcode);
                    break;
            }
        }
        return $this;
    }

}
