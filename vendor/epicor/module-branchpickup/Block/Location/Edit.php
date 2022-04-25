<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Block\Location;


/**
 * Branchpickup page search page grid
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
class Edit extends \Magento\Framework\View\Element\Template
{

    protected $_validationLocationFields = array('street1', 'city', 'country_id', 'postcode', 'telephone');

    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;


    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $country;

    /**
     * @var Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $countryCollectionFactory;
    
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $directoryCountryFactory;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;    

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Magento\Directory\Model\CountryFactory $directoryCountryFactory,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,            
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $collectionFactory,
        array $data = []
    )
    {
        $this->countryCollectionFactory = $collectionFactory;
        $this->branchPickupHelper = $branchPickupHelper;
        $this->directoryCountryFactory = $directoryCountryFactory;
        $this->directoryRegionFactory = $directoryRegionFactory;        
        $this->country = $country;
        parent::__construct(
            $context,
            $data
        );
    }


    public function getActionOfForm()
    {
        return $this->getUrl('branchpickup/pickup/savelocation');
    }

    public function checkFieldEmpty($selectedBranch)
    {
        /* @var $this Epicor_SalesRep_Block_Account_Dashboard_ErpSelector */
        $helpers = $this->branchPickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Data */
        if ($selectedBranch) {
                $details = $helpers->getPickupAddress($selectedBranch,true);
                $errors = array();
                $stateArray ='';
                foreach ($details as $key => $value) {
                if ((empty($details[$key])) && (in_array($key, $this->_validationLocationFields))) {
                    $errors[] = $key;
                }
                if(isset($details['country_id'])) {
                    $stateArray = $this->directoryCountryFactory->create()->setId($details['country_id'])->getLoadedRegionCollection()->toOptionArray(); 
                    if((!empty($stateArray)) && (!($details['region']))) {
                       array_push($errors, 'region'); 
                    }
                }
            }
        }
        
        return $errors;
    }

    public function showEmptyFields($selectedBranch)
    {
        $emptyFields = $this->checkFieldEmpty($selectedBranch);
        
        $returnEmpty = array();
        if (in_array('street1', $emptyFields)) {
            $returnEmpty['address1'] = "hideaddress1";
        }
        if (in_array('city', $emptyFields)) {
            $returnEmpty['city'] = "hidecity";
        }
        if (in_array('postcode', $emptyFields)) {
            $returnEmpty['postcode'] = "hidepostcode";
        }
        if (in_array('telephone', $emptyFields)) {
            $returnEmpty['telephone_number'] = "hidetelephone_number";
        }
        
        if ((in_array('country_id', $emptyFields)) || (in_array('region', $emptyFields))) {
            $returnEmpty['country_id'] = "hidecountry_id";
        }
        return $returnEmpty;
    }

    //M1 > M2 Translation Begin (Rule p2-1)
    public function getCountry()
    {
        return $this->country;
    }
    //M1 > M2 Translation End

    //M1 > M2 Translation Begin (Rule p2-5.2)
    public function getCountryCollection()
    {
        return $this->countryCollectionFactory->create();
    }
    //M1 > M2 Translation End
}