<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Plugin;

class ElasticsearchPlugin
{


    /**
     * Apply list & Location filtering before collection load
     *
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @param boolean $printQuery
     * @param boolean $logQuery
     *
     * @return array
     */
    public function beforeAddFieldsMapping(
        \Magento\Elasticsearch6\Model\Client\Elasticsearch $mapper,
        array $fields,
        $index,
        $entityType
    )
    {
        foreach ($fields as $field => $fieldInfo) {
            if (isset($fieldInfo['type']) && $fieldInfo['type'] == 'text') {
                $fields[$field]['analyzer'] = 'ecc_analyzer';
            }
        }

        return [$fields, $index, $entityType];
    }

}
