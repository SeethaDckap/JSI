<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists;

use Epicor\Comm\Helper\Messaging;
use Epicor\Lists\Controller\Adminhtml\Context;
use Epicor\Lists\Model\ListModel\AddressFactory;
use Epicor\Lists\Model\ResourceModel\ListModel\Address\CollectionFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\ResourceConnection;

class Restrictedaddresspost extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Lists
{
    /**
     * @var AddressFactory
     */
    protected $listsListModelAddressFactory;

    /**
     * @var RegionFactory
     */
    protected $directoryRegionFactory;
    
    /**
     * @var Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var CollectionFactory
     */
    protected $listsResourceListModelAddressCollectionFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /** @var \Epicor\Lists\Model\ListModel\Address $listAddressModel */
    private $listAddressModel;
    private $response = [];
    private $multiSelectCountries;
    private $existingCountries = [];
    private $savedCountries = [];
    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    public function __construct(
        Context $context,
        Session $backendAuthSession,
        AddressFactory $listsListModelAddressFactory,
        RegionFactory $directoryRegionFactory,
        Messaging $commMessagingHelper,
        CollectionFactory $listsResourceListModelAddressCollectionFactory,
        ResourceConnection $resourceConnection,
        CountryInformationAcquirerInterface $countryInformationAcquirer
    ) {
        $this->listsListModelAddressFactory = $listsListModelAddressFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->listsResourceListModelAddressCollectionFactory = $listsResourceListModelAddressCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        
        parent::__construct($context, $backendAuthSession);
        $this->countryInformationAcquirer = $countryInformationAcquirer;
    }
    
    public function execute()
    {
        $this->setResponseMessage('Restriction successfully saved');

        if ($data = $this->getRequest()->getPost()) {
            $this->updateRestrictions($data);
        } else {
            $this->setResponseMessage('No data found to save', 'error-msg');
        }

        $this->getResponse()->setBody($this->jsonHelper->jsonEncode($this->response));
    }

    private function updateRestrictions($data)
    {
        $addressId = $this->getRequest()->getParam('address_id');
        $this->listAddressModel = $this->listsListModelAddressFactory->create();
        /* @var $model Epicor_Lists_Model_ListModel_Address */

        try {
            if ($addressId) {
                $this->listAddressModel->load($addressId);
            }

            if (isset($data['county_id']) && !empty($data['county_id'])) {
                $region = $this->directoryRegionFactory->create()->load($data['county_id']);
                /* @var $region Mage_Directory_Model_Region */
                $data['county'] = $region->getCode();
            }
            $this->updateRestrictionData($data);
        } catch (\Exception $e) {
            $this->setResponseMessage($e->getMessage(), 'error-msg');
        }
    }

    /**
     * @param $saveData
     * @throws \Exception
     */
    private function updateRestrictionData($saveData)
    {
        if ($this->isMultiSelectCountries()) {
            $this->processMultiSelectCountries($saveData);
        } else {
            $this->saveData($saveData);
        }

        $this->setResponse();
    }

    private function setResponseMessage($message, $messageType = 'success-msg')
    {
        $this->response['type'] = $messageType;
        $this->response['message'] = __($message);
    }

    private function isMultiSelectCountries(): bool
    {
        $this->multiSelectCountries = json_decode($this->getMultiSelectCountriesParam(), true);

        return $this->isMultiSelectCountriesParamSet() && is_array($this->multiSelectCountries);
    }

    /**
     * @param $saveData
     * @throws \Exception
     */
    private function processMultiSelectCountries($saveData)
    {
        if (is_array($this->multiSelectCountries) && !empty($this->multiSelectCountries)) {
            foreach ($this->multiSelectCountries as $country) {
                $saveData['country'] = $country;
                $this->saveData($saveData);
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function setResponse()
    {
        if (empty($this->savedCountries) && !empty($this->existingCountries)) {
            $this->setResponseMessage('No restrictions saved, restrictions already exists', 'notice-msg');
        }
        if (!empty($this->savedCountries) && !empty($this->existingCountries)) {
            $this->setResponseMessage('Saved restrictions, some already existed', 'success-msg');
        }
        if (empty($this->savedCountries) && empty($this->existingCountries)) {
            $this->setResponseMessage('No restrictions saved', 'error-msg');
        }
    }

    /**
     * @param $data
     * @throws \Exception
     */
    private function saveData($data)
    {
        $this->listAddressModel->addData((array)$data);

        if ($this->listAddressModel->validateRestriction()) {
            $this->listAddressModel->save();
            $this->savedCountries[] = $data['country'];
            $this->listAddressModel->unsetData();
        } else {
            $this->existingCountries[] = $data['country'];
        }
    }

    private function isMultiSelectCountriesParamSet(): bool
    {
        return (boolean) $this->getMultiSelectCountriesParam();
    }

    private function getMultiSelectCountriesParam()
    {
        return $this->getRequest()->getParam('multiSelectCountries');
    }
}
