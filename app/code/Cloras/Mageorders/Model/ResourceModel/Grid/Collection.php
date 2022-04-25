<?php

namespace Cloras\Mageorders\Model\ResourceModel\Grid;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    )
    {
        $this->_init(
            'Cloras\Mageorders\Model\Grid',
            'Cloras\Mageorders\Model\ResourceModel\Grid'
        );
         parent::__construct(
            $entityFactory, $logger, $fetchStrategy, $eventManager, $connection,
            $resource
        );
        $this->storeManager = $storeManager;
    }
    protected function _initSelect()
    {
        // parent::_initSelect();

        $this->getSelect()->joinLeft(
                ['secondTable' => $this->getTable('sales_order')],
                'main_table.order_id = secondTable.entity_id',
                ['customer_email','customer_firstname','customer_lastname','increment_id']
            );
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('updated_at', 'main_table.updated_at');
        $this->addFilterToMap('customer_email', 'secondTable.customer_email');
        $this->addFilterToMap('customer_firstname', 'secondTable.customer_firstname');
        $this->addFilterToMap('customer_lastname', 'secondTable.customer_lastname');
        $this->addFilterToMap('increment_id', 'secondTable.increment_id');
        parent::_initSelect();
    }
}
