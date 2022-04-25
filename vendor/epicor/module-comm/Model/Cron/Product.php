<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Cron;


class Product
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogResourceModelProductCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Product\Image\Sync
     */
    protected $commProductImageSyncHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Helper\Product\Relateddocuments\Sync
     */
    protected $commProductRelatedDocSyncHelper;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory,
        \Epicor\Comm\Helper\Product\Image\Sync $commProductImageSyncHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Helper\Product\Relateddocuments\Sync $commProductRelatedDocSyncHelper
    )
    {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->commProductImageSyncHelper = $commProductImageSyncHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->commProductRelatedDocSyncHelper = $commProductRelatedDocSyncHelper;
    }

    /**
     * Function used to assign images to products in an asyncronous manner.
     */
    public function scheduleImage()
    {
        //M1 > M2 Translation Begin (Rule p2-6.10)
        //Mage::app()->setCurrentStore(\Magento\Catalog\Model\AbstractModel::DEFAULT_STORE_ID);

        //$this->storeManager->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);

        //M1 > M2 Translation End
        if (!$this->registry->registry('isSecureArea')) {
            $this->registry->register('isSecureArea', true);
        }

        if ($this->scopeConfig->isSetFlag('Epicor_Comm/image_cron/schedule', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $maxProducts = $this->scopeConfig->getValue('Epicor_Comm/image_cron/products_per_run', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $collection = $this->catalogResourceModelProductCollectionFactory->create();
            $collection->setFlag('no_locations_filtering', true);
            $collection->addAttributeToSelect('ecc_erp_images_processed', 'left');
            $collection->addAttributeToSelect('ecc_erp_images_last_processed');
            /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
            $collection->addAttributeToFilter(array(
                array('attribute' => 'ecc_erp_images_processed', 'null' => 1),
                array('attribute' => 'ecc_erp_images_processed', 'eq' => 0)
            ));
            $collection->addAttributeToSort('ecc_erp_images_last_processed', 'ASC');
            $collection->setPage(1, $maxProducts);

            $products = $collection->getItems();

            $helper = $this->commProductImageSyncHelper;
            /* @var $helper Epicor_Comm_Helper_Product_Image_Sync */

            $assetsFolder = $helper->getAssetsFolder();
            if ($helper->validateOrCreateDirectory($assetsFolder)) {
                foreach ($products as $productInfo) {
                    /* @var $productInfo Varien_Object */
                    $productId = $this->catalogProductFactory->create()->setStoreId(0)->getIdBySku($productInfo->getSku());
                    $helper->processErpImages($productId);
                }
            } else {
                $helper->sendMagentoMessage(
                    "Directory $assetsFolder was not found and cannot be created due to permissions, must be created manually.", "Products Assets Folder not created", \Magento\AdminNotification\Model\Inbox::SEVERITY_CRITICAL
                );
            }
        }

        $this->registry->unregister('isSecureArea');
    }

    /**
     * Function sync related documents for Products
     */
    public function scheduleRelatedDocument()
    {
        if (!$this->registry->registry('isSecureArea')) {
            $this->registry->register('isSecureArea', true);
        }

        if ($this->scopeConfig->isSetFlag('epicor_product_config/product_related_document_sync/sync_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $maxProducts = $this->scopeConfig->getValue('epicor_product_config/product_related_document_sync/products_per_run', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $collection = $this->catalogResourceModelProductCollectionFactory->create();
            $collection->setFlag('no_locations_filtering', true);
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter(array(
                array('attribute' => 'ecc_related_documents_synced', 'null' => 1),
                array('attribute' => 'ecc_related_documents_synced', 'eq' => 0)
            ),null, 'left');
            $collection->addAttributeToFilter(array(
                array('attribute' => 'ecc_related_documents', 'neq' => 'a:0:{}')
            ),null, 'left');

//            $collection->addAttributeToFilter(
//                'ecc_related_documents',
//                ['neq' => 'a:0:{}']
//            );

            $collection->setPage(1, $maxProducts);
            $products = $collection->getItems();
            $helper = $this->commProductRelatedDocSyncHelper;
            /* @var $helper \Epicor\Comm\Helper\Product\Relateddocuments\Sync */

            $assetsFolder = $helper->getAssetsFolder();

            if ($helper->validateOrCreateDirectory($assetsFolder)) {
                foreach ($products as $productInfo) {
                    /* @var $productInfo Varien_Object */
                    $productId = $this->catalogProductFactory->create()->setStoreId(0)->getIdBySku($productInfo->getSku());
                    $helper->processRelatedDocuments($productId);
                }
            } else {
                $helper->sendMagentoMessage(
                    "Directory $assetsFolder was not found and cannot be created due to permissions, must be created manually.", "Products Assets Folder not created", \Magento\AdminNotification\Model\Inbox::SEVERITY_CRITICAL
                );
            }
        }
    }

}
