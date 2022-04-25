<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\Adapter\BatchDataMapper;

use Epicor\Elasticsearch\Model\ResourceModel\Index;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;

class LocationFieldsProvider implements AdditionalFieldsProviderInterface
{

    const ECC_LOCATION_CODE_FIELD_NAME = "ecc_location_code";

    /**
     * @var Index
     */
    private $resourceIndex;


    /**
     * LocationFieldsProvider constructor.
     * @param Index $resourceIndex
     */
    public function __construct(
        Index $resourceIndex
    ) {
        $this->resourceIndex = $resourceIndex;
    }

    /**
     * @inheritdoc
     */
    public function getFields(array $productIds, $storeId)
    {
        $locationData = $this->resourceIndex->getFullLocationProductIndexData($storeId, $productIds);

        $fields = [];
        foreach ($productIds as $productId) {
            $fields[$productId] = $this->getProductLocationData($productId, $locationData);
        }

        return $fields;
    }

    /**
     * Prepare location index data for product
     *
     * @param int $productId
     * @param array $categoryIndexData
     * @return array
     */
    private function getProductLocationData($productId, array $locationIndexData)
    {
        $result = [];

        if (array_key_exists($productId, $locationIndexData)) {
            $indexData = $locationIndexData[$productId];
            $locationIds = array_column($indexData, 'location_code');

            if (count($locationIds)) {
                $result = [SELF::ECC_LOCATION_CODE_FIELD_NAME => $locationIds];
            }
        }

        if (count($result) == 0) {
            $result = [SELF::ECC_LOCATION_CODE_FIELD_NAME => null];
        }

        return $result;
    }
}
