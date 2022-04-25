<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\ResourceModel\Groups;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Model Collection Class for OrderApproval
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 */
class Collection extends AbstractCollection
{

    protected $groupedById = false;

    /**
     * @var string
     */
    protected $_idFieldName = 'group_id';

    public function _construct()
    {
        $this->_init('Epicor\OrderApproval\Model\Groups',
            'Epicor\OrderApproval\Model\ResourceModel\Groups');
    }

    /**
     * Adds Active filter to the Collection
     *
     * @return \Epicor\OrderApproval\Model\ResourceModel\Groups\Collection
     */
    public function filterActive()
    {
        $this->addFieldToFilter('is_active', 1);

        return $this;
    }

    /**
     * @return $this
     */
    public function groupById()
    {
        if ( ! $this->groupedById) {
            $this->getSelect()->group('main_table.group_id');
            $this->groupedById = true;
        }

        return $this;
    }

    /**
     * (Fix) using group() breaks getSelectCountSql in magento
     *  Wrong count in admin Grid when using GROUP BY clause, overriding lib module
     *
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Zend_Db_Select::ORDER);
        $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(\Zend_Db_Select::COLUMNS);
        $countSelect->reset(\Zend_Db_Select::HAVING);

        if (count($this->getSelect()->getPart(\Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(\Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(\Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT ".implode(", ", $group).")");
        } else {
            $countSelect->columns('COUNT(*)');
        }

        return $countSelect;
    }
}
