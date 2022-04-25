<?php

namespace Cloras\Magecustomers\Model\ResourceModel\Grid;

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
            'Cloras\Magecustomers\Model\Grid',
            'Cloras\Magecustomers\Model\ResourceModel\Grid'
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
                ['secondTable' => $this->getTable('customer_entity')],
                'main_table.customer_id = secondTable.entity_id',
                ['email','firstname','lastname']
            );
        $this->addFilterToMap('customer_id', 'main_table.customer_id');
        $this->addFilterToMap('updated_at', 'main_table.updated_at');
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('email', 'secondTable.email');
        $this->addFilterToMap('firstname', 'secondTable.firstname');
        $this->addFilterToMap('lastname', 'secondTable.lastname');
        parent::_initSelect();
    }
}
