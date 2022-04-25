<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Cataloginventory\Stock;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Item extends \Magento\CatalogInventory\Model\Stock\Item
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;


    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commHelper = $commHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->storeManager = $storeManager;
        $this->commLocationsHelper = $commLocationsHelper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $customerSession,
            $storeManager,
            $stockConfiguration,
            $stockRegistry,
            $stockItemRepository,
            $resource,
            $resourceCollection,
            $data
        );
    }
    
    public function getBackorders()
    {
        $helper = $this->commHelper;
        /* @var $helper \Epicor\Comm\Helper\Data */
        $erpAccount = $helper->getErpAccountInfo();

        if (!empty($erpAccount)) {
            $backOrders = $erpAccount->getAllowBackorders();
            /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
        }
        //M1 > M2 Translation Begin (Rule 31)
        //if ($backOrders || $this->storeManager->getStore()->isAdmin()) {
        if ($erpAccount->getAllowBackorders() ||  $this->storeManager->getStore()->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
        //M1 > M2 Translation End
            return parent::getBackorders();
        }
        return \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO;
    }

}
