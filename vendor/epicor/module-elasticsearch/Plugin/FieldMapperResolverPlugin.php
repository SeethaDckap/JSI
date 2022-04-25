<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Plugin;

class FieldMapperResolverPlugin
{

    /**
     * add location field to attribute mapper
     *
     * @param \Magento\Elasticsearch\Model\Adapter\FieldMapper\FieldMapperResolver $subject
     * @param $result
     * @return mixed
     */
    public function afterGetAllAttributesTypes(
        \Magento\Elasticsearch\Model\Adapter\FieldMapper\FieldMapperResolver $subject,
        $result
    ) {
        $result[\Epicor\Elasticsearch\Model\Adapter\BatchDataMapper\LocationFieldsProvider::ECC_LOCATION_CODE_FIELD_NAME] = [
            "type" => "keyword",
            "null_value" => "NULL"
        ];
        return $result;
    }
}