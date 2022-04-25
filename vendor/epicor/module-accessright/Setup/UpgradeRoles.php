<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Setup;

use InvalidArgumentException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Epicor\AccessRight\Model\ResourceModel\Rules\CollectionFactory as RulesCollectionFactory;
use Epicor\AccessRight\Model\ResourceModel\RoleModel\CollectionFactory as RoleCollectionFactory;
use Epicor\AccessRight\Model\RoleModel as AccessRightRole;
use Epicor\AccessRight\Model\ResourceModel\RoleModel\Collection as AccessRightRoleCollection;

class UpgradeRoles
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var array
     */
    private $roleData;

    /**
     * @var RulesCollectionFactory
     */
    private $rulesCollectionFactory;

    /**
     * @var RoleCollectionFactory
     */
    private $roleCollectionFactory;

    /**
     * UpgradeRoles constructor.
     * @param RulesCollectionFactory $rulesCollectionFactory
     * @param RoleCollectionFactory $roleCollectionFactory
     */
    public function __construct(
        RulesCollectionFactory $rulesCollectionFactory,
        RoleCollectionFactory $roleCollectionFactory
    ) {
        $this->rulesCollectionFactory = $rulesCollectionFactory;
        $this->roleCollectionFactory = $roleCollectionFactory;
    }

    /**
     * Used to update existing access roles to be used along
     * with data setup
     *
     * @param array $dataRows
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function updateNewRoleResources($dataRows, $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $accessRoles = $this->getAccessRoles();

        foreach ($accessRoles as $role) {
            /** @var AccessRightRole $role */
            $this->updateRoles($dataRows, $role);
        }
    }

    /**
     * @return AccessRightRoleCollection
     */
    private function getAccessRoles()
    {
        $roleCollection = $this->roleCollectionFactory->create();

        return $roleCollection->addFieldToSelect('id');
    }

    /**
     * @param array $dataRows
     * @param AccessRightRole $role
     */
    private function updateRoles($dataRows, $role)
    {
        foreach ($dataRows as $dataRow) {
            $this->setRoleData($role, $dataRow);
            if (!$this->isExistingResource($role)) {
                $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                    $this->moduleDataSetup->getTable('ecc_access_role_rule'),
                    $this->roleData,
                    [
                        'access_role_id',
                        'resource_id',
                        'permission'
                    ]
                );
            }
        }
    }

    /**
     * @param array $resourceIds
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function removeRoleRules($resourceIds, $moduleDataSetup)
    {
        foreach ($resourceIds as $resourceId) {
            $moduleDataSetup->getConnection()->delete(
                $moduleDataSetup->getTable('ecc_access_role_rule'),
                ['resource_id = ?' => $resourceId]
            );
        }
    }

    /**
     * @param AccessRightRole $role
     * @return bool
     */
    private function isExistingResource($role)
    {
        $rulesCollection = $this->rulesCollectionFactory->create();
        $rulesCollection
            ->addFieldToFilter('resource_id', $this->roleData['resource_id'])
            ->addFieldToFilter('access_role_id', $role->getId());

        return $rulesCollection->getSize() > 0;
    }

    /**
     * @param AccessRightRole $role
     * @param array $dataRow
     */
    private function setRoleData($role, $dataRow)
    {
        if (!$role->getId() || !isset($dataRow['resource_id'])) {
            throw new InvalidArgumentException('Required role data not set');
        }
        $this->roleData = [];
        $this->roleData['access_role_id'] = $role->getId();
        $this->roleData['resource_id'] = $dataRow['resource_id'];
        $this->roleData['permission'] = 'allow';
    }
}