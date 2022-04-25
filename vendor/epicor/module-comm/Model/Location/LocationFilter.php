<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Location;

use \Epicor\Comm\Model\Product;

/**
 * ECC should only list the locations that
 * have been returned in the MSQ response from ERP - WSO-7666
 * Class LocationFilter
 * @package Epicor\Comm\Model\Location
 */
class LocationFilter
{
    /**
     * @var array
     */
    private $msqLocationCodes = [];

    /**
     * @var array
     */
    private $productLocations = [];

    /**
     * @var Product
     */
    private $product;

    /**
     * Exclude product location not getting from MSQ
     *
     * @param array $productLocations
     * @param Product $product
     * @return array
     */
    public function filterMsqLocations($productLocations, $product)
    {
        $this->msqLocationCodes = [];
        if (!$product instanceof Product) {
            return $productLocations;
        }
        $this->product = $product;
        if (!$this->setLocationCodesFromMsq()) {
            return $productLocations;
        }
        $this->productLocations = $productLocations;
        if (is_array($this->productLocations)) {
            return $this->getFilteredMsqLocation();
        }
        return [];
    }

    /**
     * @return array|bool
     */
    private function setLocationCodesFromMsq()
    {
        $msqData = $this->product->getData('msq_message_data');
        if (!$msqData) {
            return false;
        }
        $locationDataFromMsq = $msqData['locations'] ?? '';

        if (is_array($locationDataFromMsq)) {
            foreach ($locationDataFromMsq as $locationData) {
                $this->setMsqLocationCodes($locationData);
            }
        }

        return $this->msqLocationCodes;
    }

    /**
     * @param array $locationData
     */
    private function setMsqLocationCodes($locationData)
    {
        if (isset($locationData['locationCode'])) {
            $this->msqLocationCodes[] = $locationData['locationCode'] ?? '';
        } else if (is_array($locationData)) {
            foreach ($locationData as $location) {
                $this->msqLocationCodes[] = $location['locationCode'] ?? '';
            }
        }
    }

    /**
     * @return array
     */
    private function getFilteredMsqLocation()
    {
        if (is_array($this->productLocations)) {
            return $this->filterLocationKeys();
        }
        return [];
    }

    /**
     * @return array
     */
    private function filterLocationKeys()
    {
        $displayLocation = [];
        foreach ($this->productLocations as $locationKey => $productLocation) {
            if (in_array($locationKey, $this->msqLocationCodes, false)) {
                $displayLocation[$locationKey] = $productLocation;
            }
        }

        return $displayLocation;
    }
}