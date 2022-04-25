<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\ResourceModel\Boost;

use Magento\Store\Model\Store;
use Epicor\Elasticsearch\Api\Data\BoostInterface;

/**
 * Boost Collection Resource Model
 *
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store for filter
     *
     * @var integer
     */
    private $storeId;

    /**
     * @var string
     */
    protected $_idFieldName = 'boost_id';

    /**
     * Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

        $this->date = $date;
    }

    /**
     * Set Store ID for filter
     *
     * @param Store|int $store
     *
     * @return $this
     */
    public function setStoreId($store)
    {
        if ($store instanceof Store) {
            $store = $store->getId();
        }
        $this->storeId = $store;
        return $this;
    }

    /**
     * Retrieve Store ID Filter.
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * Returns only active boost.
     *
     * @param string $date
     *
     * @return \Epicor\Elasticsearch\Model\ResourceModel\Boost\Collection
     */
    public function addIsActiveFilter($date = null)
    {
        $this->addFieldToFilter('is_active', true);
        if ($date == null) {
            $date = $this->date->date('Y-m-d');
        }
        $this->getSelect()
            ->where('from_date is null or from_date <= ?', $date)
            ->where('to_date is null or to_date >= ?', $date);
        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(
            'Epicor\Elasticsearch\Model\Boost',
            'Epicor\Elasticsearch\Model\ResourceModel\Boost'
        );
    }
}
