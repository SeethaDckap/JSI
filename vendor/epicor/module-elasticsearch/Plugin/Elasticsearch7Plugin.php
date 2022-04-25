<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Plugin;

class Elasticsearch7Plugin
{

    /**
     * Apply Custom analyzer for special char
     *
     * @param \Magento\Elasticsearch7\Model\Client\Elasticsearch $mapper
     * @param array $fields
     * @param string $index
     * @param string $entityType
     * @return void
     */
    public function beforeAddFieldsMapping(
        \Magento\Elasticsearch7\Model\Client\Elasticsearch $mapper,
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
