<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Setup\Patch\Data;

use Epicor\AccessRight\Model\ResourceModel\Rules\CollectionFactory;
use Epicor\Customerconnect\Model\Skus\CpnuManagement;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class CheckAccountSkuAccess
 * @package Epicor\Database\Setup\Patch\Data
 */
class CheckAccountSkuAccess implements DataPatchInterface
{
    /**
     * @var CollectionFactory
     */
    private $collection;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * CheckAccountSkuAccess constructor.
     * @param CollectionFactory $collection
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        CollectionFactory $collection,
        ResourceConnection $resourceConnection
    ) {
        $this->collection = $collection;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $table = 'ecc_access_role_rule';
        $roleIds = $this->getUniqueAccessIds();
        $data = array();
        foreach ($roleIds as $roleId) {
            $roleData = $roleId->getData();
            $id = $roleData['access_role_id'];
            array_push($data, array(
                'access_role_id' => $id,
                'resource_id' => CpnuManagement::FRONTEND_RESOURCE_ACCOUNT_SKU_ADD,
                'permission' => 'allow',
            ));
            array_push($data, array(
                'access_role_id' => $id,
                'resource_id' => CpnuManagement::FRONTEND_RESOURCE_ACCOUNT_SKU_EDIT,
                'permission' => 'allow',
            ));
            array_push($data, array(
                'access_role_id' => $id,
                'resource_id' => CpnuManagement::FRONTEND_RESOURCE_ACCOUNT_SKU_DELETE,
                'permission' => 'allow',
            ));
        }
        if (empty($data) === false) {
            $tableName = $this->resourceConnection->getTableName($table);
            $this->resourceConnection->getConnection()->insertMultiple($tableName, $data);
        }
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    private function getUniqueAccessIds()
    {
        return $this->collection->create()
            ->addFieldToSelect('access_role_id')->distinct(true)->getItems();
    }
}
