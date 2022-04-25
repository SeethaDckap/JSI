<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model;


/**
 * Add To lists supporting class
 *
 *
 */
class AddProductToLists
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    private $catalogResourceModelProductFactoryExist = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    private $catalogResourceModelProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    private $commLocationsHelper;


    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogResourceModelProductFactory
     * @param \Epicor\Comm\Helper\Locations $commLocationsHelper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogResourceModelProductFactory,
        \Epicor\Comm\Helper\Locations $commLocationsHelper
    )
    {
        $this->catalogResourceModelProductFactory = $catalogResourceModelProductFactory;
        $this->storeManager = $storeManager;
        $this->commLocationsHelper = $commLocationsHelper;

    }

    /**
     * @return \Epicor\Comm\Helper\Locations
     */
    public function getLocationsHelper()
    {
        return $this->commLocationsHelper;
    }

    /**
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }


    /**
     * @return \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    public function catalogResourceModelProductFactory()
    {
        if (!$this->catalogResourceModelProductFactoryExist) {
            $this->catalogResourceModelProductFactoryExist = $this->catalogResourceModelProductFactory->create();
        }
        return $this->catalogResourceModelProductFactoryExist;
    }

    /*
     * Get products Skus
     */
    public function getSkuByIds($productIds)
    {
        $connection = $this->catalogResourceModelProductFactory()->getConnection();
        $table = $connection->getTableName('catalog_product_entity');
        $sql = $connection->select()
            ->from(['main_table' => $table], ['entity_id', 'sku'])
            ->where('entity_id IN (' . implode(',', $productIds) . ')');
        return $connection->fetchAssoc($sql);
    }

}
