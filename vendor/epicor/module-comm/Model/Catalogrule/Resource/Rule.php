<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Catalogrule\Resource;


/**
 * Overidden - Catalog rules resource model
 *
 * @category    Epicor
 * @package     Epicor_Comm
 * @author      Web Sales Team
 */
class Rule extends \Magento\CatalogRule\Model\ResourceModel\Rule
{
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\ConditionFactory $conditionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogRule\Helper\Data $catalogRuleData,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $storeManager,
            $conditionFactory,
            $coreDate,
            $eavConfig,
            $eventManager,
            $catalogRuleData,
            $logger,
            $dateTime,
            $priceCurrency,
            $connectionName
        );
    }


    /**
     * Get active rule data based on few filters
     *
     * - OVERIDDEN - Core has no sort order!!!
     * 
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $productId
     * @return array
     */
    public function getRulesFromProduct($date, $websiteId, $customerGroupId, $productId)
    {
        $adapter = $this->getConnection();
        if (is_string($date)) {
            $date = strtotime($date);
        }
        $select = $adapter->select()
            ->from($this->getTable('catalogrule_product'))
            ->where('website_id = ?', $websiteId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id = ?', $productId)
            ->where('from_time = 0 or from_time < ?', $date)
            ->where('to_time = 0 or to_time > ?', $date)
            ->order('sort_order ASC');

        return $adapter->fetchAll($select);
    }

}
