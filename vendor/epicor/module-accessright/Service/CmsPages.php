<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Service;

use Epicor\AccessRight\Model\ResourceModel\Rules\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class CmsPages
 * @package Epicor\AccessRight\Service
 */
class CmsPages
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
     * CmsPages constructor.
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
     * @param $pageId
     */
    public function removePageFromRule($pageId)
    {
        $table = 'ecc_access_role_rule';

        $tableName = $this->resourceConnection->getTableName($table);
        $this->resourceConnection->getConnection()->delete(
            $tableName,
            [
                'resource_id = ?' => 'Epicor_CMS::cms_' . $pageId
            ]
        );
    }

    /**
     * @param $id
     */
    public function addCmsPageInRule($pageId)
    {
        $table = 'ecc_access_role_rule';
        $roleIds = $this->getAccessIds();
        $data = array();
        foreach ($roleIds as $roleId) {
            $roleData = $roleId->getData();
            $id = $roleData['access_role_id'];
            array_push($data, array(
                'access_role_id' => $id,
                'resource_id' => 'Epicor_CMS::cms_' . $pageId,
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
    private function getAccessIds()
    {
        return $this->collection->create()
            ->addFieldToSelect('access_role_id')->distinct(true)->getItems();
    }
}
