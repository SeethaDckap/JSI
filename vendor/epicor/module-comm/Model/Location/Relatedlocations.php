<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Location;


/**
 * Related Location model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method \Epicor\Comm\Model\Location\Relatedlocations setLocationId(integer $value)
 * @method \Epicor\Comm\Model\Location\Relatedlocations setRelatedLocationId(integer $value)
 * 
 * @method integer getLocationId()
 * @method integer getRelatedLocationId()
 */
class Relatedlocations extends \Epicor\Common\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory
     */
    protected $commResourceLocationCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\LocationFactory
     */
    protected $commLocationFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Model\ResourceModel\Location\CollectionFactory $commResourceLocationCollectionFactory,
        \Epicor\Comm\Model\LocationFactory $commLocationFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->scopeConfig = $scopeConfig;
        $this->commResourceLocationCollectionFactory = $commResourceLocationCollectionFactory;
        $this->commLocationFactory = $commLocationFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Location\Relatedlocations');
    }
    
/**
     * Get Related Location
     * 
     * @param int $locationId
     * @return Epicor\Comm\Model\ResourceModel\Location\Collection
     */
    public function getRelatedLocations($locationId)
    {
        $stockVisibility = $this->scopeConfig->getValue('epicor_comm_locations/global/stockvisibility', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $addCondition = "";
        switch ($stockVisibility) {
            case 'default':
            case 'logged_in_shopper_source':
                $locationId = 0;
                break;
            case 'locations_to_include':
                $locationsToInclude = $this->scopeConfig->getValue('epicor_comm_locations/global/locations_to_include', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $locationCodesToInclude = explode(',', $locationsToInclude);
                $addCondition = " and main_table.code in ('" . implode("', '", $locationCodesToInclude) . "')";
                break;
            case 'locations_to_exclude':
                $locationsToExclude = $this->scopeConfig->getValue('epicor_comm_locations/global/locations_to_exclude', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $locationCodesToExclude = explode(',', $locationsToExclude);
                $addCondition = " and main_table.code not in ('" . implode("', '", $locationCodesToExclude) . "')";
                break;
        }
        $locations = $this->commResourceLocationCollectionFactory->create();
        $relatedLocationsTable = $locations->getTable('ecc_location_relatedlocations');
        $locations->getSelect()
                        ->join(
                            array('related_location' => $relatedLocationsTable),
                            'main_table.id = related_location.related_location_id',
                            array('related_location.related_location_id' => 'related_location_id')
                        )
                        ->where('related_location.location_id = ' . $locationId . $addCondition);
        return $locations;
    }

    public function getRelatedLocationsByCode($locationCode)
    {
        if (!$this->_registry->registry('related_locations_'.$locationCode)) {
            $location = $this->commLocationFactory->create()->load($locationCode, 'code');
            $relatedLocations = $this->getRelatedLocations($location->getId());
            $relatedLocationCodes = [];
            foreach ($relatedLocations as $relLocation) {
                $relatedLocationCodes[] = $relLocation->getCode();
            }
            $this->_registry->unregister('related_locations_' . $locationCode);
            $this->_registry->register('related_locations_'.$locationCode, $relatedLocationCodes);
        }
        $relatedLocationCodes = $this->_registry->registry('related_locations_'.$locationCode);
        return $relatedLocationCodes;
    }

    public function getRelatedLocationsForProduct($location)
    {
        //$location = $this->commLocationFactory->create()->load($locationCode, 'code');
        $relatedLocations = $this->getRelatedLocations($location->getId());
        $relatedLocationCodes = array();
        foreach ($relatedLocations as $relLocation) {
            $relatedLocationCodes[$relLocation->getCode()]['include_inventory'] = $relLocation->getIncludeInventory();
            $relatedLocationCodes[$relLocation->getCode()]['show_inventory'] = $relLocation->getShowInventory();
            $relatedLocationCodes[$relLocation->getCode()]['location_visible'] = $relLocation->getLocationVisible();
        }
        return $relatedLocationCodes;
    }
}
