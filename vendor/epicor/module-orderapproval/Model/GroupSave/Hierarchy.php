<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\GroupSave;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\RequestInterface;
use Epicor\OrderApproval\Model\GroupSave\Utilities;
use Epicor\OrderApproval\Model\GroupSave\Groups as GroupSave;

class Hierarchy
{
    private $postData;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $linkTableName;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var \Epicor\OrderApproval\Model\GroupSave\Utilities
     */
    private $utilities;
    /**
     * @var Groups
     */
    private $groupSave;

    /**
     * Hierarchy constructor.
     * @param ResourceConnection $resourceConnection
     * @param RequestInterface $request
     * @param \Epicor\OrderApproval\Model\GroupSave\Utilities $utilities
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        RequestInterface $request,
        Utilities $utilities,
        GroupSave $groupSave
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->request = $request;
        $this->utilities = $utilities;
        $this->groupSave = $groupSave;
    }

    /**
     * @return void
     */
    public function saveHierarchy()
    {
        $this->connection = $this->resourceConnection->getConnection();
        $this->linkTableName = $this->connection->getTableName('ecc_approval_group_link');
        if (!$this->isUpdate()) {
            $this->saveParent();
            $this->saveChildGroups();
        } else {
            $this->updateParent();
            $this->updateChildGroups();
        }
    }

    /**
     * @return string
     */
    private function isUpdate()
    {
        return $this->getGroupId();
    }

    /**
     * @return string
     */
    private function getGroupId()
    {
        $data = $this->utilities->getPostData();
        return $data['group_id_val'] ?? '';
    }

    /**
     * @return void
     */
    private function saveParent()
    {
        if ($selectedParent = $this->getSelectedParent()) {
            $this->insertParentGroup($selectedParent);
        }
    }

    /**
     * @return void
     */
    private function saveChildGroups()
    {
        $ids = $this->getChildGroupIds();
        if (!empty($ids) && is_array($ids)) {
            foreach ($ids as $childId) {
                $this->insertChildGroup($childId);
            }
        }
    }

    /**
     * @param $childId
     * @param $groupId
     */
    private function insertChildGroup($childId)
    {
        $id = $this->groupSave->getMainGroupId();
        $sql = "INSERT INTO $this->linkTableName (group_id, parent_group_id, by_group, by_customer) 
                        VALUES ('$childId',' $id  ','0','1')";
        $this->connection->query($sql);
    }

    /**
     * @return array
     */
    private function getChildGroupIds()
    {
        $hierarchyChildGroupIds = [];
        $data = $this->utilities->getPostData();
        if (isset($data['child_groups'])) {
            $hierarchyChildren = $data['child_groups'];
            foreach ($hierarchyChildren as $ref => $group) {
                if (is_numeric($ref)) {
                    $hierarchyChildGroupIds[] = $group;
                }
            }
        }
        return $hierarchyChildGroupIds;
    }

    /**
     * @return void
     */
    private function updateParent()
    {
        if ($selectedParent = $this->getSelectedParent()) {
            if (!$this->isParentSet()) {
                $this->insertParentGroup($selectedParent);
            } else {
                $this->updateParentGroup($selectedParent);
            }
        }
    }

    /**
     * @param $groupId
     * @return string
     */
    private function isParentSet()
    {
        $id = $this->groupSave->getMainGroupId();
        $sql = "SELECT group_id FROM $this->linkTableName WHERE group_id = $id";
        return $this->connection->fetchOne($sql);
    }

    /**
     * @param $selectedParent
     * @return void
     */
    private function updateParentGroup($selectedParent)
    {
        $id = $this->groupSave->getMainGroupId();
        $sql = "UPDATE  $this->linkTableName SET parent_group_id = $selectedParent WHERE group_id = $id";
        $this->connection->query($sql);
    }

    /**
     * @return void
     */
    private function updateChildGroups()
    {
        $ids = $this->getChildGroupIds();

        if (!empty($ids) && is_array($ids)) {
            $this->clearExistingChildGroups();
            foreach ($ids as $childId) {
                $this->insertChildGroup($childId);
            }
        }
        if ($this->isSectionLoaded() && empty($ids)){
            $this->clearExistingChildGroups();
        }
    }

    private function isSectionLoaded()
    {
        return (boolean) $this->utilities->getPostData()['hierarchy-loaded'] ?? 0;
    }

    /**
     * @return void
     */
    private function clearExistingChildGroups()
    {
        $id = $this->groupSave->getMainGroupId();
        $sql = "DELETE FROM  $this->linkTableName WHERE parent_group_id = $id";
        $this->connection->query($sql);
    }

    /**
     * @return string
     */
    private function getSelectedParent()
    {
        $data = $this->utilities->getPostData();
        return $data['groupselect'] ?? '';
    }

    /**
     * @param $selectedParent
     * @return void
     */
    private function insertParentGroup($selectedParent)
    {
        $id = $this->groupSave->getMainGroupId();
        $sql = "INSERT INTO $this->linkTableName (group_id, parent_group_id, by_group, by_customer) 
                    VALUES ('$id','$selectedParent','0','1')";
        $this->connection->query($sql);
    }
}