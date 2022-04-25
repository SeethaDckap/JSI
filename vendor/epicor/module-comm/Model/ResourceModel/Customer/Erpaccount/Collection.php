<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel\Customer\Erpaccount;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }



    protected function _construct()
    {
        // define which resource model to use
        $this->_init('Epicor\Comm\Model\Customer\Erpaccount', 'Epicor\Comm\Model\ResourceModel\Customer\Erpaccount');
    }

    public function toOptionArray()
    {
        $this->addOrder('name', 'ASC');
        return $this->_toOptionArray('entity_id');
    }

    public function joinLocationLinkInfo($locationCode)
    {
        $table = $this->getTable('ecc_location_link');
        $entityType = \Epicor\Comm\Model\Location\Link::ENTITY_TYPE_ERPACCOUNT;
        //M1 > M2 Translation Begin (Rule 39)
        // $locationCode = $this->resourceConnection->getConnection('default_write')->quote($locationCode);
        $locationCode = $this->resourceConnection->getConnection()->quote($locationCode);
        //M1 > M2 Translation End

        $this->getSelect()->joinLeft(array('loc' => $table), 'loc.entity_id=main_table.entity_id AND loc.entity_type="' . $entityType . '" AND loc.location_code=' . $locationCode . '', array('link_type' => 'link_type'), null, 'left');
        $this->getSelect()->group('main_table.entity_id');
    }

}
