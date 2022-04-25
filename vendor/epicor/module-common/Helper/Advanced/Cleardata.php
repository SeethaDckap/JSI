<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper\Advanced;


use Magento\Framework\EntityManager\MetadataPool;

/**
 * Clear data helper, contains funcitons for clearing various data types
 *
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
class Cleardata extends \Epicor\Common\Helper\Data
{

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Epicor\Faqs\Model\ResourceModel\Vote\CollectionFactory
     */
    protected $faqsResourceVoteCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quotesResourceQuoteCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $salesResourceModelOrderCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var MetadataPool
     * @since 101.0.0
     */
    protected $metadataPool;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;


    public function __construct(
        \Epicor\Common\Helper\Context $context,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        MetadataPool $metadataPool,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Epicor\Faqs\Model\ResourceModel\Vote\CollectionFactory $faqsResourceVoteCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Quotes\Model\ResourceModel\Quote\CollectionFactory $quotesResourceQuoteCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesResourceModelOrderCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->faqsResourceVoteCollectionFactory = $faqsResourceVoteCollectionFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->quotesResourceQuoteCollectionFactory = $quotesResourceQuoteCollectionFactory;
        $this->salesResourceModelOrderCollectionFactory = $salesResourceModelOrderCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }


    /**
     * Deletes all product data from the system
     *
     * Because of facing below issue ::
     *You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near 'SET FOREIGN_KEY_CHECKS = 1' at line 2
     * FOREIGN_KEY_CHECKS Need to disabled & enabled in seperate Query
     */
    public function clearProducts()
    {
        $query = [
            'TRUNCATE TABLE `catalog_product_bundle_option`;',
            'TRUNCATE TABLE `catalog_product_bundle_option_value`;',
            'TRUNCATE TABLE `catalog_product_bundle_selection`;',
            'TRUNCATE TABLE `catalog_product_entity_datetime`;',
            'TRUNCATE TABLE `catalog_product_entity_decimal`;',
            'TRUNCATE TABLE `catalog_product_entity_gallery`;',
            'TRUNCATE TABLE `catalog_product_entity_int`;',
            'TRUNCATE TABLE `catalog_product_entity_media_gallery`;',
            'TRUNCATE TABLE `catalog_product_entity_media_gallery_value`;',
            'TRUNCATE TABLE `catalog_product_entity_text`;',
            'TRUNCATE TABLE `catalog_product_entity_tier_price`;',
            'TRUNCATE TABLE `catalog_product_entity_varchar`;',
            'TRUNCATE TABLE `catalog_product_link`;',
            'TRUNCATE TABLE `catalog_product_link_attribute_decimal`;',
            'TRUNCATE TABLE `catalog_product_link_attribute_int`;',
            'TRUNCATE TABLE `catalog_product_link_attribute_varchar`;',
            'TRUNCATE TABLE `catalog_product_link_type`;',
            'TRUNCATE TABLE `catalog_product_option`;',
            'TRUNCATE TABLE `catalog_product_option_price`;',
            'TRUNCATE TABLE `catalog_product_option_title`;',
            'TRUNCATE TABLE `catalog_product_option_type_price`;',
            'TRUNCATE TABLE `catalog_product_option_type_title`;',
            'TRUNCATE TABLE `catalog_product_option_type_value`;',
            'TRUNCATE TABLE `catalog_product_super_attribute`;',
            'TRUNCATE TABLE `catalog_product_super_attribute_label`;',
            /* // Not exist in Magento 2.0
             *  'TRUNCATE TABLE `catalog_product_super_attribute_pricing`;',
             *  'TRUNCATE TABLE `catalog_product_enabled_index`;', */
            'TRUNCATE TABLE `ecc_erp_account_sku`;',
            'TRUNCATE TABLE `catalog_product_super_link`;',
            'TRUNCATE TABLE `catalog_product_website`;',
            'TRUNCATE TABLE `catalog_product_entity`;',
            'TRUNCATE TABLE `cataloginventory_stock`;',
            'TRUNCATE TABLE `cataloginventory_stock_item`;',
            'TRUNCATE TABLE `cataloginventory_stock_status`;',
            'TRUNCATE TABLE `catalog_product_link`;',
            'TRUNCATE TABLE `catalog_product_option`;',
            'TRUNCATE TABLE `catalog_product_option_type_value`;',
            'alter table `catalog_product_option_type_value` AUTO_INCREMENT=1;',
            'TRUNCATE TABLE `catalog_product_super_attribute`;',
            'TRUNCATE TABLE `catalog_product_entity`;',
            'TRUNCATE TABLE `cataloginventory_stock`;',
            'TRUNCATE TABLE `catalog_category_product`;',
            'TRUNCATE TABLE `catalogrule_product`;',
            'INSERT INTO `catalog_product_link_type` VALUES (1, "relation"), (3, "super"), (4, "up_sell"), (5, "cross_sell"), (7, "substitute");',
            'INSERT INTO `cataloginventory_stock`(`stock_id`,`stock_name`) VALUES (1,"Default");',

            'delete from `catalog_category_product` WHERE product_id not in(select entity_id from catalog_product_entity);',
            'delete from `catalog_category_product` WHERE category_id not in(select entity_id from catalog_category_entity);',
            'delete from `catalog_product_website` WHERE product_id not in(select entity_id from catalog_product_entity);',
            'delete from `catalog_product_entity_media_gallery` WHERE value_id not in(select entity_id from catalog_product_entity);',
            'delete from `catalog_product_index_eav_idx` WHERE entity_id not in(select entity_id from catalog_product_entity);',
            'delete from `catalog_product_index_eav` WHERE entity_id not in(select entity_id from catalog_product_entity);',
            'delete from `catalog_product_link` WHERE product_id not in(select entity_id from catalog_product_entity);',
            'delete from `catalog_product_relation` WHERE parent_id not in(select entity_id from catalog_product_entity);',
            'delete from `ecc_entity_register` 
            WHERE type IN ("Related", "UpSell", "CrossSell", "CustomerSku", "CategoryProduct", "Product");',
            'DELETE FROM `url_rewrite` WHERE  entity_type = "product";',
            'TRUNCATE TABLE `catalog_product_entity_media_gallery_value_to_entity`;',
            'TRUNCATE TABLE `ecc_location_product`;',
            'TRUNCATE TABLE `ecc_location_product_currency`;'
        ];

        $setting =  $this->scopeConfig->getValue('catalog/frontend/flat_catalog_product');

        if(!is_null($setting) && $setting==1){
            foreach ($this->storeManager->getStores() as $store) {

                $query []= 'delete from `catalog_product_flat_' . $store->getId() . '` WHERE entity_id not in(select entity_id from catalog_product_entity);
                ';
            }
        }

        $this->_runQuery($query);


        $categoryMetadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\CategoryInterface::class);
        $categoryLinkField = $categoryMetadata->getLinkField();
        $edition = $this->productMetadata->getEdition();
        $edition = strtolower($edition);
        if($edition == 'enterprise'){
            $queryE = [
                'TRUNCATE TABLE `sequence_product`;',
                'TRUNCATE TABLE `sequence_product_bundle_option`;',
                'TRUNCATE TABLE `sequence_product_bundle_selection`;'
            ];
            $this->_runQuery($queryE);
        }


    }

    /**
     * Deletes all category data from the system
     */
    public function clearCategories()
    {
        $query = [
            'TRUNCATE TABLE `catalog_category_entity`;',
            'TRUNCATE TABLE `catalog_category_entity_datetime`;',
            'TRUNCATE TABLE `catalog_category_entity_decimal`;',
            'TRUNCATE TABLE `catalog_category_entity_int`;',
            'TRUNCATE TABLE `catalog_category_entity_text`;',
            'TRUNCATE TABLE `catalog_category_entity_varchar`;',
            'TRUNCATE TABLE `catalog_category_product`;',
            'TRUNCATE TABLE `catalog_category_product_index`;',
            'DELETE FROM `url_rewrite` WHERE  entity_type = "category";'];

        $this->_runQuery($query);

        $categoryMetadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\CategoryInterface::class);
        $categoryLinkField = $categoryMetadata->getLinkField();
        $edition = $this->productMetadata->getEdition();
        $edition = strtolower($edition);

        $query =array();
        /* Insertion on below table need FOREIGN KEY CHECK enabled */
        if($edition == 'enterprise'){
            $queryE = [
                'TRUNCATE TABLE `sequence_catalog_category`;'
            ];
            $this->_runQuery($queryE);

            $query =[
                "INSERT INTO `sequence_catalog_category`
					(`sequence_value`) 
				VALUES 
					(1),
					(2);","INSERT INTO `catalog_category_entity`
					(`entity_id`,`created_in`,`updated_in`,`attribute_set_id`,`parent_id`,`created_at`,`updated_at`,`path`,`position`,`level`,`children_count`) 
				VALUES 
					(1,1,".\Magento\Staging\Model\VersionManager::MAX_VERSION.",3,0,NOW(),NOW(),'1',0,0,1),
					(2,1,".\Magento\Staging\Model\VersionManager::MAX_VERSION.",3,1,NOW(),NOW(),'1/2',1,1,0);",
                "INSERT INTO `catalog_category_entity_int`
					(`value_id`,`attribute_id`,`store_id`,`".$categoryLinkField."`,`value`) 
				VALUES 
					(1,69,0,1,1),
					(2,46,0,2,1);",
                "INSERT INTO `catalog_category_entity_varchar`
					(`value_id`,`attribute_id`,`store_id`,`".$categoryLinkField."`,`value`) 
				VALUES 
					(1,45,0,1,'Root Catalog'),
					(2,45,0,2,'Default Category'),
					(3,52,0,2,'PRODUCTS');",
                "Delete from `ecc_entity_register` 
				WHERE type IN ('CategoryProduct','Category');"

            ];

        }else{
            $query =[
                "INSERT INTO `catalog_category_entity`
					(`entity_id`,`attribute_set_id`,`parent_id`,`created_at`,`updated_at`,`path`,`position`,`level`,`children_count`) 
				VALUES 
					(1,3,0,NOW(),NOW(),'1',0,0,1),
					(2,3,1,NOW(),NOW(),'1/2',1,1,0);",
                "INSERT INTO `catalog_category_entity_int`
					(`value_id`,`attribute_id`,`store_id`,`".$categoryLinkField."`,`value`) 
				VALUES 
					(1,69,0,1,1),
					(2,46,0,2,1);",
                "INSERT INTO `catalog_category_entity_varchar`
					(`value_id`,`attribute_id`,`store_id`,`".$categoryLinkField."`,`value`) 
				VALUES 
					(1,45,0,1,'Root Catalog'),
					(2,45,0,2,'Default Category'),
					(3,52,0,2,'PRODUCTS');",
                "Delete from `ecc_entity_register` 
				WHERE type IN ('CategoryProduct','Category');"

            ];
        }
        $this->_runQuery($query,TRUE);
    }

    /**
     * Deletes all erp account data from the system
     */
    public function clearErpaccounts()
    {
        $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $collection Epicor_Comm_Model_Resource_Customer_Erpaccount_Collection */

        foreach ($collection->getItems() as $erpAccount) {
            $erpAccount->delete();
        }
    }

    /**
     * Deletes all customer data from the system
     */
    public function clearCustomers()
    {

        $collection = $this->faqsResourceVoteCollectionFactory->create();

        foreach ($collection->getItems() as $vote) {
            $vote->delete();
        }

        $this->clearSupplierData();

        $this->clearArpayments();

        $collection = $this->customerResourceModelCustomerCollectionFactory->create();

        foreach ($collection->getItems() as $customer) {
            $customer->delete();
        }
    }

    /**
     * Deletes all quotes data from the system
     */
    public function clearQuotes()
    {
        $collection = $this->quotesResourceQuoteCollectionFactory->create();

        foreach ($collection->getItems() as $quote) {
            $quote->delete();
        }
    }

    /**
     * Deletes all order data from the system
     */
    public function clearOrders()
    {
        $collection = $this->salesResourceModelOrderCollectionFactory->create();

        foreach ($collection->getItems() as $order) {
            $order->delete();
        }
    }

    /**
     *
     */
    public function clearLocations()
    {
        $query = [
            'TRUNCATE TABLE `ecc_location_product_currency`;',
            'TRUNCATE TABLE `ecc_location_product`;',
            'TRUNCATE TABLE `ecc_location_link`;',
            'TRUNCATE TABLE `ecc_location`;'
        ];
        $this->_runQuery($query);
    }

    /**
     *
     */
    public function clearArpayments()
    {
        $query = [
            'TRUNCATE TABLE `ecc_ar_quote`;',
            'TRUNCATE TABLE `ecc_ar_quote_address`;',
            'TRUNCATE TABLE `ecc_ar_quote_item`;',
            'TRUNCATE TABLE `ecc_ar_quote_payment`;',
            'TRUNCATE TABLE `ecc_ar_sales_order`;',
            'TRUNCATE TABLE `ecc_ar_sales_order_address`;',
            'TRUNCATE TABLE `ecc_ar_sales_order_grid`;',
            'TRUNCATE TABLE `ecc_ar_sales_order_item`;',
            'TRUNCATE TABLE `ecc_ar_sales_order_payment`;',
            'TRUNCATE TABLE `ecc_ar_sales_order_status_history`;',
            'TRUNCATE TABLE `ecc_ar_sales_payment_transaction`;'
        ];
        $this->_runQuery($query);
    }


    public function clearSupplierData()
    {
        $query = [
            'TRUNCATE TABLE `ecc_supplier_reminder`;',
            'TRUNCATE TABLE `ecc_supplier_dashboard`;'
        ];
        $this->_runQuery($query);
    }

    /**
     * Deletes all return data from the system
     */
    public function clearReturns()
    {
        $query = [
            'DELETE FROM `ecc_file` 
                WHERE id 
                    IN(
                        SELECT attachment_id FROM ecc_customer_return_attachment
                    );',

            'DELETE FROM `ecc_customer_return`;',
            'DELETE FROM `ecc_customer_return_line`;',
            'DELETE FROM `ecc_customer_return_attachment`;'
        ];
        $this->_runQuery($query);
    }

    /**
     * Deletes all lists data from the system
     */
    public function clearLists()
    {
        $query = [
            'TRUNCATE TABLE `ecc_contract`;',
            'TRUNCATE TABLE `ecc_contract_product`;',
            'TRUNCATE TABLE `ecc_list`;',
            'TRUNCATE TABLE `ecc_list_address`;',
            'TRUNCATE TABLE `ecc_list_brand`;',
            'TRUNCATE TABLE `ecc_list_customer`;',
            'TRUNCATE TABLE `ecc_list_erp_account`;',
            'TRUNCATE TABLE `ecc_list_label`;',
            'TRUNCATE TABLE `ecc_list_product`;',
            'TRUNCATE TABLE `ecc_list_product_price`;',
            'TRUNCATE TABLE `ecc_list_store_group`;',
            'TRUNCATE TABLE `ecc_list_website`;'
        ];

        $this->_runQuery($query);
    }

    private function _runQuery($query, $needFkcheck=FALSE)
    {
        /**
         * Get the resource model
         */
        $resource = $this->resourceConnection;

        /**
         * Retrieve the write connection
         */
        $writeConnection = $resource->getConnection('core_write');

        /**
         * Execute the query
         */

        if(!$needFkcheck){
            $DisableFkeyquery = <<<SQL
                SET FOREIGN_KEY_CHECKS = 0;
SQL;
            $writeConnection->query($DisableFkeyquery);
        }

        foreach($query as $qry){
            $writeConnection->query($qry);
        }

        $EnableFkeyquery = <<<SQL
            SET FOREIGN_KEY_CHECKS = 1;
SQL;
        $writeConnection->query($EnableFkeyquery);
    }

}