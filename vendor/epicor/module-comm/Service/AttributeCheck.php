<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Service;

use Magento\Framework\App\ResourceConnection;

/**
 * Class AttributeCheck
 * @package Epicor\Comm\Service
 */
class AttributeCheck
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * AttributeCheck constructor.
     * @param ResourceConnection $connection
     */
    public function __construct(
        ResourceConnection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * To check if a particular attribute exists or not
     *
     * @param $code
     * @return string
     */
    public function isAttributeExists($code)
    {
        $column = 'attribute_code';
        $tableName = $this->connection->getConnection()->getTableName('eav_attribute');
        $sqlQuery = $this->connection->getConnection()->select()
            ->distinct()
            ->from($tableName, $column)
            ->where('attribute_code = ?', $code);

        return $this->connection->getConnection()->fetchOne($sqlQuery);
    }
}
