<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ResourceModel\ListModel\Address;


/**
 * Model Collection Class for List Address
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Collection extends \Epicor\Database\Model\ResourceModel\Lists\Address\Collection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }


    public function _construct()
    {
        $this->_init('Epicor\Lists\Model\ListModel\Address','Epicor\Lists\Model\ResourceModel\ListModel\Address');
    }

    /**
     * Adds Active filter to the Collection
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filterActive()
    {
        $this->addFieldToFilter('activation_date', array(
            //M1 > M2 Translation Begin (Rule 25)
            //array('lteq' => now()),
            array('lteq' => date('Y-m-d H:i:s')),
            //M1 > M2 Translation End
            array('null' => 1),
            array('eq' => '0000-00-00 00:00:00'),
        ));

        $this->addFieldToFilter('expiry_date', array(
            //M1 > M2 Translation Begin (Rule 25)
            //array('gteq' => now()),
            array('gteq' => date('Y-m-d H:i:s')),
            //M1 > M2 Translation End
            array('null' => 1),
            array('eq' => '0000-00-00 00:00:00'),
        ));

        return $this;
    }

    /**
     * Filters addresses by newest activation date
     *
     * @param array $listIds
     * @return $this
     */
    public function filterByActivationDate($order, $listIds)
    {
        $condition = $order == 'newest' ? 'MAX' : 'MIN';
        $subQuery = $this->getActivationSubQuery($condition, $listIds);
        $this->getSelect()->where('activation_date = (' . $subQuery . ')');
        return $this;
    }

    /**
     * Builds subquery for finding max / min activation date
     *
     * @param string $mode
     * @param array $listIds
     *
     * @return string
     */
    protected function getActivationSubQuery($mode, $listIds)
    {
        $filteredLists = array();
        $connection = $this->getConnection();
        foreach ($listIds as $listId) {
            $filteredLists[] = $connection->quote($listId);
        }

        $tableName = $this->getTable('ecc_list_address');
        $subQuery = 'SELECT
			' . $mode . '(CAST(activation_date AS CHAR)) admax
		FROM
			`' . $tableName . '` AS `m2`
		WHERE
		(`list_id` IN (' . implode(',', $filteredLists) . ')) LIMIT 1';

        return $subQuery;
    }

}
