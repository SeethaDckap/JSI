<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\ResourceModel\Rules;

/**
 * Rules collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Epicor\AccessRight\Model\Rules::class,
            \Epicor\AccessRight\Model\ResourceModel\Rules::class
        );
    }

    /**
     * Get rules by role id
     *
     * @param int $roleId
     * @return $this
     */
    public function getByRoles($roleId)
    {
        $this->addFieldToFilter('access_role_id', (int)$roleId);
        return $this;
    }

    /**
     * Sort by length
     *
     * @return $this
     */
    public function addSortByLength()
    {
        $length = $this->getConnection()->getLengthSql('{{resource_id}}');
        $this->addExpressionFieldToSelect('length', $length, 'resource_id');
        $this->getSelect()->order('length ' . \Magento\Framework\DB\Select::SQL_DESC);

        return $this;
    }
}
