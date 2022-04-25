<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\AccessRight\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Model Resource Class for Role
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 */
class RoleModel extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ecc_access_role', 'id');
    }

    /**
     * Get the Roles collection
     *
     * @param string $erpLinkType
     * @return array
     */
    public function getRolesCollection($erpLinkType)
    {
        $_erpLinkTypes = [
            $erpLinkType,
            \Epicor\AccessRight\Model\RoleModel::ERP_ACC_LINK_TYPE_CHOSEN,
            \Epicor\AccessRight\Model\RoleModel::ERP_ACC_LINK_TYPE_NONE
        ];
        $connection = $this->getConnection();
        $_erpAccLinkTypes = '"' . implode('", "', $_erpLinkTypes) . '"';
        $tableName = $this->getMainTable();
        $sql = $connection->select()->from($this->getMainTable())
            ->where('erp_account_link_type IN (' . $_erpAccLinkTypes . ') AND active = 1 ');
        $roles = $connection->fetchAll($sql);
        return $roles;
    }


    /**
     * Get the Role based on priorty from list of roles ids
     *
     * @param array $roleids
     * @return int
     */

    public function getValidRolesFromRoles($rolesids = false)
    {
        $connection = $this->getConnection();
        $select = $this->loadActive();
        if ($rolesids && is_array($rolesids)) {
            $select->where(
                'id IN ( ? )',
                $rolesids
            );
        }
        return $connection->fetchAll($select);
    }

    public function getRoleFromRoles($rolesids = false)
    {
        $connection = $this->getConnection();
        $select = $this->loadActive();
        $select->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['id']);
        if ($rolesids && is_array($rolesids)) {
            $select->where(
                'id IN ( ? )',
                $rolesids
            );
        }
        return $connection->fetchOne($select);
    }

    public function loadActive()
    {
        $date = date('Y-m-d H:i:s');
        $select = $this->getSelectRoleTable()->where('active = ?', 1);
        $expire = '(((`start_date` <= \'' . $date . '\') OR 
        (`start_date` IS NULL) OR
         (`start_date` = \'0000-00-00 00:00:00\'))) AND (((`end_date` >= \'' . $date . '\') OR
          (`end_date` IS NULL) OR (`end_date` = \'0000-00-00 00:00:00\')))';

        $select->where($expire);
        $select->order('priority DESC');
        $select->order('id DESC');
        return $select;
    }

    public function getSelectRoleTable()
    {
        return $this->getConnection()->select()->from(['main_table' => $this->getMainTable()]);
    }
}
