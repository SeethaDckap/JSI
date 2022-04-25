<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;


/**
 * Response LOC - Upload Location
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Loc extends \Epicor\Comm\Model\Message\Upload
{

    protected $_locationCodes = array();
    protected $_exists;
    protected $_errorLocations = array();

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->commLocationFactory = $commLocationFactory;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setConfigBase('epicor_comm_field_mapping/loc_mapping/');
        $this->setMessageType('LOC');
        $this->setLicenseType('Customer');
        $this->setMessageCategory(self::MESSAGE_CATEGORY_LOCATION);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');

    }

    /**
     * Process an upload
     * 
     * @return 
     */
    public function processAction()
    {
        $this->erpData = $this->getRequest();
        /* @var $this->erpData \Epicor\Common\Model\Xmlvarien */

        if (!$this->erpData) {
            $error = $this->getErrorDescription(self::STATUS_GENERAL_ERROR, 'No Valid Body');
            $code = self::STATUS_GENERAL_ERROR;
            throw new \Exception($error, $code);
        }

        $erpLocations = $this->_getGroupedData('locations', 'location', $this->erpData);

// Removing the check for empty locations tag.
//        if (empty($erpLocations)) {
//            $this->_throwMissingError($this->erpData, 'location');
//        }

        $this->_loadStores($this->erpData);
        $this->_processLocations($erpLocations);

        $codes = implode(',', $this->_locationCodes);
        $this->setMessageSubject($codes);
    }

    /**
     * Processed locations sent up by the ERP
     * 
     * @param array $erpLocations
     */
    private function _processLocations($erpLocations)
    {
        foreach ($erpLocations as $erpLocation) {
            $this->_processLocation($erpLocation);
        }

        if (!empty($this->_errorLocations)) {
            $code = self::STATUS_LOCATION_NOT_ON_FILE;
            $error = $this->getErrorDescription($code, '"' . implode('", "', $this->_errorLocations) . "'");
            throw new \Exception($error, $code);
        }
    }

    /**
     * Processes uploaded location
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpLocation
     */
    private function _processLocation($erpLocation)
    {
        $eccLocation = $this->commLocationFactory->create();
        /* @var $eccLocation \Epicor\Comm\Model\Location */

        if (!$erpLocation) {
            $error = $this->getErrorDescription(self::STATUS_GENERAL_ERROR, 'No Location Data Provided');
            $code = self::STATUS_GENERAL_ERROR;
            throw new \Exception($error, $code);
        }

        if (!$erpLocation->getLocationCode()) {
            $error = $this->getErrorDescription(self::STATUS_INVALID_LOCATION_CODE, $erpLocation->getLocationCode());
            $code = self::STATUS_INVALID_LOCATION_CODE;
            throw new \Exception($error, $code);
        }

        $this->_locationCodes[] = $erpLocation->getLocationCode();

        $eccLocation->load($erpLocation->getLocationCode(), 'code');

        $this->_exists = !$eccLocation->isObjectNew();
        $att = $erpLocation->getData('_attributes');

        if ($att && $att->getDelete() == 'Y') {
            $this->_deleteLocation($eccLocation, $erpLocation);
        } else {
            $this->_updateLocation($eccLocation, $erpLocation);
        }
    }

    /**
     * Deletes the given location
     * 
     * @param \Epicor\Comm\Model\Location $eccLocation
     * @param \Epicor\Common\Model\Xmlvarien $erpLocation
     */
    private function _deleteLocation($eccLocation, $erpLocation)
    {
        if (!$eccLocation->isObjectNew()) {
            $eccLocation->delete();
        } 
    }

    /**
     * Updates an ECC location from the ERP data
     * 
     * @param \Epicor\Comm\Model\Location $eccLocation
     * @param \Epicor\Common\Model\Xmlvarien $erpLocation
     */
    private function _updateLocation($eccLocation, $erpLocation)
    {
        $helper = $this->commMessagingHelper->create();
        /* @var $helper \Epicor\Comm\Helper\Messaging */
        if ($eccLocation->isObjectNew()) {
            $eccLocation->setCode($erpLocation->getLocationCode());
        }

        if ($_company = $this->getCompany()) {
            $eccLocation->setCompany($_company);
        }

        if ($this->isUpdateable('name_update', $this->_exists)) {
            $eccLocation->setName($erpLocation->getName());
        }

        if ($this->isUpdateable('address1_update', $this->_exists)) {
            //M1 > M2 Translation Begin (Rule 9)
            //$eccLocation->setAddress1($erpLocation->getAddress1());
            $eccLocation->setData('address1', $erpLocation->getData('address1'));
            //M1 > M2 Translation End
        }

        if ($this->isUpdateable('address2_update', $this->_exists)) {
            //M1 > M2 Translation Begin (Rule 9)
            //$eccLocation->setAddress2($erpLocation->getAddress2());
            $eccLocation->setData('address2', $erpLocation->getData('address2'));
            //M1 > M2 Translation End
        }

        if ($this->isUpdateable('address3_update', $this->_exists)) {
            //M1 > M2 Translation Begin (Rule 9)
            //$eccLocation->setAddress3($erpLocation->getAddress3());
            $eccLocation->setData('address3', $erpLocation->getData('address3'));
            //M1 > M2 Translation End
        }

        if ($this->isUpdateable('city_update', $this->_exists)) {
            $eccLocation->setCity($erpLocation->getCity());
        }

        if ($this->isUpdateable('county_update', $this->_exists)) {
            $eccLocation->setCountyCode($erpLocation->getCounty());
        }

        if ($this->isUpdateable('country_update', $this->_exists)) {
            $country = $helper->getCountryCodeMapping($erpLocation->getCountry(), $helper::ERP_TO_MAGENTO);
            $eccLocation->setCountry($country);
        }

        if ($this->isUpdateable('postcode_update', $this->_exists)) {
            $eccLocation->setPostcode($erpLocation->getPostcode());
        }

        if ($this->isUpdateable('telephone_update', $this->_exists)) {
            $eccLocation->setTelephoneNumber($erpLocation->getTelephoneNumber());
        }

        if ($this->isUpdateable('fax_update', $this->_exists)) {
            $eccLocation->setFaxNumber($erpLocation->getFaxNumber());
        }

        if ($this->isUpdateable('email_update', $this->_exists)) {
            $eccLocation->setEmailAddress($erpLocation->getEmailAddress());
        }

        if ($this->isUpdateable('mobile_update', $this->_exists)) {
            $eccLocation->setMobileNumber($erpLocation->getMobileNumber());
        }

        if ($this->isUpdateable('delivery_method_update', $this->_exists)) {
            $methodCodes = $this->_getErpLocationMethodCodes($erpLocation);
            $eccLocation->setDeliveryMethodCodes(serialize($methodCodes));
        }

        if ($this->isUpdateable('stores_update', $this->_exists)) {

            $storeIds = array();
            if ($this->_stores) {
                foreach ($this->_stores as $store) {
                    /* @var $store Mage_Core_Model_Store */
                    $storeIds[] = $store->getGroupId();
                }
            }

            $eccLocation->setFullStores($storeIds);
        }
        if ($this->isUpdateable('location_visible', $this->_exists)) {
            $eccLocation->setLocationVisible(1);
        }
        if ($this->isUpdateable('include_inventory', $this->_exists)) {
            $eccLocation->setIncludeInventory(1);
        }
        if ($this->isUpdateable('show_inventory', $this->_exists)) {
            $eccLocation->setShowInventory(1);
        }
        if (!$eccLocation->getSource()) {
            $eccLocation->setSource('erp');
        }

        $eccLocation->setDummy(0);
        $eccLocation->setSortOrder(0);

        $eccLocation->save();
    }

    /**
     * Gets an array of included / excluded locaitons from the given ERP data
     * 
     * @param \Epicor\Common\Model\Xmlvarien $erpLocation
     * 
     * @return array
     */
    private function _getErpLocationMethodCodes($erpLocation)
    {
        $methodCodes = $this->_getGroupedData('delivery_method_codes', 'delivery_method_code', $erpLocation);

        $included = array();
        $excluded = array();

        foreach ($methodCodes as $code) {
            $att = $code->getData('_attributes');
            if (!$att || $att->getIncluded() == 'Y') {
                $included[] = $code->getValue();
            } else {
                $excluded[] = $code->getValue();
            }
        }

        if (!empty($included) || !empty($excluded)) {
            $methods = array(
                'included' => $included,
                'excluded' => $excluded,
            );
        } else {
            $methods = null;
        }

        return $methods;
    }

    /**
     * Throws an exception due to missing data
     * 
     * @param \Epicor\Common\Model\Xmlvarien $object
     * @param string $tag
     * 
     * @throws \Exception
     */
    private function _throwMissingError($object, $tag)
    {
        if ($object->hasData($tag)) {
            $error = $this->getErrorDescription(self::STATUS_GENERAL_ERROR, 'No Locations Provided');
            $code = self::STATUS_GENERAL_ERROR;
        } else {
            $error = $this->getErrorDescription(self::STATUS_XML_TAG_MISSING, $tag);
            $code = self::STATUS_XML_TAG_MISSING;
        }

        throw new \Exception($error, $code);
    }

}
