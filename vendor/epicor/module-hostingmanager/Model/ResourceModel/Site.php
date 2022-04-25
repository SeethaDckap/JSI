<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Site resource model
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Site extends AbstractDb
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $connectionName
        );
    }
    
    protected function _construct()
    {
        $this->_init('ecc_hosting_site', 'entity_id');
    }
    
    
    /**
     * @todo Make this part of the common module and change all epicor model to extend the new abstract
     */
    public function loadByAttributes($attributes)
    {
        $adapter = $this->getConnection();
        $where = array();
        foreach ($attributes as $attributeCode => $value) {
            $where[] = sprintf('%s=:%s', $attributeCode, $attributeCode);
        }
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where(implode(' AND ', $where));

        $binds = $attributes;

        return $adapter->fetchRow($select, $binds);
    }

}
