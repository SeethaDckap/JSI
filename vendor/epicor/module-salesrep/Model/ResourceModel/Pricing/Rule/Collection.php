<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\ResourceModel\Pricing\Rule;


class Collection extends \Epicor\Database\Model\ResourceModel\Salesrep\Pricing\Rule\Collection
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
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
        //$this->_init('epicor_salesrep_pricing_rule');
        $this->_init('Epicor\SalesRep\Model\Pricing\Rule','Epicor\SalesRep\Model\ResourceModel\Pricing\Rule');
    }

    public function filterCurrentlyActiveOnly()
    {
        $this->addFieldToFilter('is_active', 1);
        $this->addFieldToFilter('from_date', array(
            //M1 > M2 Translation Begin (Rule 25)
            //array('lteq' => now()),
            array('lteq' => date('Y-m-d H:i:s')),
            //M1 > M2 Translation End
            array('null' => 1),
            array('eq' => '0000-00-00 00:00:00'),
        ));

        $this->addFieldToFilter('to_date', array(
            //M1 > M2 Translation Begin (Rule 25)
            //array('gteq' => now()),
            array('gteq' => date('Y-m-d H:i:s')),
            //M1 > M2 Translation End
            array('null' => 1),
            array('eq' => '0000-00-00 00:00:00'),
        ));
    }

    public function filterByApplicableProduct($productId)
    {
        // join the store values
        $this->getSelect()
            ->joinLeft(array(
                'p' => $this->getTable('ecc_salesrep_pricing_rule_product')
                ), 'p.pricing_rule_id = main_table.id AND p.product_id = "' . $productId . '"', array('is_valid')
            )
            ->where('p.is_valid IS NOT NULL')
            ->where('p.store_id', $this->storeManager->getStore()->getId())
            ->group('main_table.id');
    }

}
