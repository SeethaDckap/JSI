<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Upload;


/**
 * Response SGP - Upload Stock Category Groups
 * 
 * Assign a product to a group, product and group need to exist on the site
 *
 * XML Data Support
 * productCode          - supported
 * appendOrReplace      - supported
 * productGroup         - supported
 *   
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Sgp extends \Epicor\Comm\Model\Message\Upload
{

    CONST SGP_APPEND_GROUPS = 'A';
    CONST SGP_REPLACE_GROUPS = 'R';
    CONST SGP_DELETE_GROUPS = 'D';

    protected $_maxDeadlockRetriesDefault = 5;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $catalogResourceModelCategoryCollectionFactory;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;
    protected $_indexProducts = array();

    /**
     * @var \Epicor\Comm\Helper\Entityreg
     */
    protected $commEntityregHelper;
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $catalogResourceModelCategoryCollectionFactory,
        \Epicor\Comm\Helper\Entityreg $commEntityregHelper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->catalogResourceModelCategoryCollectionFactory = $catalogResourceModelCategoryCollectionFactory;
        $this->commProductHelper = $context->getCommProductHelper();
        $this->commEntityregHelper = $commEntityregHelper;

        $this->configurable = $configurable;

        parent::__construct($context, $resource, $resourceCollection, $data);

        $this->setConfigBase('epicor_comm_field_mapping/sgp_mapping/');
        $this->setMessageType('SGP');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_PRODUCT);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');

    }

    /**
     * Process a request
     *
     * @return 
     */
    public function processAction()
    {
        $this->erpData = $this->getRequest();

        $productCode = $this->erpData->getProductCode();
        $this->setMessageSubject($productCode);

        $this->_loadStores();

        $product = $this->catalogProductFactory->create();
        /* @var $product \Magento\Catalog\Model\Product */

        $productId = $product->getIdBySku($productCode);
        $product = $this->catalogProductFactory->create()->load($productId);

        $configurableProduct = null;
        $configurableProductCategoriesIds = array();
        $IsConfigurableProductExist = false;
        $updatedCategoriesForConfigurable = array();

        if (!$product->isObjectNew()) {

            if($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
                $configurableProductIds = (array)$this->configurable->getParentIdsByChild($product->getId());
                if(!empty($configurableProductIds) && is_array($configurableProductIds) && isset($configurableProductIds[0])){
                    $configurableProductId = $configurableProductIds[0];
                    $configurableProduct = $this->catalogProductFactory->create()->load($configurableProductId);
                    if (!$configurableProduct->isObjectNew()) {
                        $IsConfigurableProductExist = true;
                        $this->_indexProducts[] = $configurableProductId;
                    }

                    $configurableProductCategoriesIds = $configurableProduct->getCategoryIds();
                }
            }

            $this->_indexProducts[] = $product->getId();
            $productGroups = $this->erpData->getProductGroups();
            $productGroupAttributes = $productGroups->getData('_attributes');
            $append = $productGroupAttributes ? $productGroupAttributes->getAppendOrReplace() : 'A';
            $categories = $product->getCategoryIds();

            if (empty($categories)) {
                $categories = array();
            }

            $updatedCategories = array();

            if ($append == self::SGP_REPLACE_GROUPS) {
                foreach ($categories as $categoryId) {
                    $category = $this->catalogCategoryFactory->create()->load($categoryId);
                    /* @var $category \Magento\Catalog\Model\Category */
                    if ($category &&
                        !$category->isObjectNew() &&
                        !$category->getEccErpCode() &&
                        count(array_intersect($category->getStoreIds(), $this->_storeIds)) >= 1
                    ) {
                        $updatedCategories[] = $category->getId();
                    }
                }

                /*
                 * IN CASE OF REPLACE-R: Filter those category from configurable Product
                 * which is needed to replaced from child Product Category
                 */
                if(!empty($configurableProductCategoriesIds) && is_array($configurableProductCategoriesIds)){
                    foreach ($configurableProductCategoriesIds as $cid){
                        if(!in_array($cid,$categories)){
                            $updatedCategoriesForConfigurable[] = $cid;
                        }
                    }
                }
            } else {
                $updatedCategories = $categories;
            }

            $groups = $productGroups->getasarrayProductGroup();
            $groupsToBeRemoved = array();

            foreach ($groups as $groupCode) {
                if (!empty($groupCode)) {

                    $category = $this->catalogCategoryFactory->create()->loadByAttribute('ecc_erp_code', $groupCode);

                    $erpCategories = $this->catalogResourceModelCategoryCollectionFactory->create()
                        ->addFieldToFilter('ecc_erp_code', array('eq' => $groupCode))
                        ->addAttributeToSelect('*');

                    $items = $erpCategories->getItems();

                    if ($append == self::SGP_DELETE_GROUPS) {
                        // groups in msg are to be deleted
                        if (!empty($items)) {
                            foreach ($items as $category) {
                                if (count(array_intersect($category->getStoreIds(), $this->_storeIds)) >= 1) {
                                    $groupsToBeRemoved[] = $category->getId();

                                    $parents = $category->getParentIds();

                                    if (!empty($parents)) {
                                        foreach ($parents as $parentId) {
                                            $groupsToBeRemoved[] = $parentId;
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        // groups in message are to be added to the category 
                        if (!empty($items)) {
                            foreach ($items as $category) {
                                // group code found in the category table add it and parents to array.
                                if (count(array_intersect($category->getStoreIds(), $this->_storeIds)) >= 1) {
                                    $updatedCategories[] = $category->getId();
                                    if($IsConfigurableProductExist) {
                                        $updatedCategoriesForConfigurable[] = $category->getId();
                                    }
                                }
                            }
                        } else {
                            throw new \Exception(
                            $this->getErrorDescription(self::STATUS_PRODUCT_GROUP_NOT_ON_FILE, $groupCode), self::STATUS_PRODUCT_GROUP_NOT_ON_FILE
                            );
                        }
                    }
                }
            }

            // remove any groups flagged for deletion
            if (!empty($groupsToBeRemoved)) {
                $updatedCategories = array();
                foreach ($categories as $categoryId) {
                    if (!in_array($categoryId, $groupsToBeRemoved)) {
                        $updatedCategories[] = $categoryId;
                    }
                }
                // Below code for Configurable Parent Product Categories remove
                $updatedCategoriesForConfigurable = array();
                if(!empty($configurableProductCategoriesIds)){
                    foreach ($configurableProductCategoriesIds as $categoryId){
                        if (!in_array($categoryId, $groupsToBeRemoved)) {
                            $updatedCategoriesForConfigurable[] = $categoryId;
                        }
                    }
                }
            }

            // process all category ID's in the updated messaging to ensure that category parents are added too
            $uniqueCategories = array_unique($updatedCategories);

            if (!empty($uniqueCategories)) {
                foreach ($uniqueCategories as $categoryId) {
                    $category = $this->catalogCategoryFactory->create()->load($categoryId);
                    /* @var $category \Magento\Catalog\Model\Category */

                    $parents = $category->getParentIds();

                    if (!empty($parents)) {
                        foreach ($parents as $parentId) {
                            //M1 > M2 Translation Begin (Rule 33)
                            //if (!in_array($parentId, $uniqueCategories)) {
                            if (!in_array($parentId, $uniqueCategories) && $parentId!= \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                                //M1 > M2 Translation End
                                $uniqueCategories[] = $parentId;
                            }
                        }
                    }
                }
            }
            // If configurable Product exist then do same
            // process all category ID's in the updated messaging to ensure that category parents are added too
            $updatedCategoriesForConfigurable = array_unique($updatedCategoriesForConfigurable);

            if (!empty($updatedCategoriesForConfigurable)) {
                foreach ($updatedCategoriesForConfigurable as $categoryId) {
                    $category = $this->catalogCategoryFactory->create()->load($categoryId);
                    /* @var $category \Magento\Catalog\Model\Category */
                    $parents = $category->getParentIds();
                    if (!empty($parents)) {
                        foreach ($parents as $parentId) {
                            if (!in_array($parentId, $updatedCategoriesForConfigurable) && $parentId!= \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                                $updatedCategoriesForConfigurable[] = $parentId;
                            }
                        }
                    }
                }
            }

//            foreach ($this->_stores as $store) {
//
//                $product = Mage::getModel('catalog/product')->setStoreId($store->getId())->load($productId);
//                /* @var $product Mage_Catalog_Model_Product */
//
//                $product->setCategoryIds($uniqueCategories);
//                $product->save();
//            }

            $product->setCategoryIds($uniqueCategories);
            $product->save();

            //Save configurable
            if($IsConfigurableProductExist){
                if($append == self::SGP_APPEND_GROUPS){
                    $updatedCategoriesForConfigurable = array_merge($uniqueCategories, $configurableProductCategoriesIds);
                    $configurableProduct->setCategoryIds($updatedCategoriesForConfigurable);
                }else{
                    // IF Any other case $append == self::SGP_DELETE_GROUPS OR self::SGP_REPLACE_GROUPS
                    $configurableProduct->setCategoryIds($updatedCategoriesForConfigurable);
                }
                $configurableProduct->save();
            }

            $this->updateEntityRegistrations($product->getId(), $categories, $uniqueCategories);

            $commProductHelper = $this->commProductHelper->create();
           // $commProductHelper->reindexProduct($product);
            if ($product->getTypeId() == 'grouped') {
                $originalLinkedProducts = array_values($product->getTypeInstance()->getChildrenIds($product->getId()));
                $originalLinkedProducts = $originalLinkedProducts[0];
                foreach ($originalLinkedProducts as $originalLinkedProduct) {
                    $child = $this->catalogProductFactory->create()->load($originalLinkedProduct);
                    $child->setCategoryIds($uniqueCategories);
                    $child->save();
                    $this->_indexProducts[] = $child->getId();
                  //  $commProductHelper->reindexProduct($child);
                }
            }

        } else {
            if ($this->erpData->hasVarienDataFromPath('product_code')) {
                $errorType = self::STATUS_PRODUCT_NOT_ON_FILE;
                $errorMsg = $productCode;
            } else {
                $errorType = self::STATUS_XML_TAG_MISSING;
                $errorMsg = 'productCode';
            }

            throw new \Exception(
            $this->getErrorDescription($errorType, $errorMsg), $errorType
            );
        }
    }

    public function afterProcessAction()
    {
        $this->_index();
        parent::afterProcessAction();
    }
    /**
     * Indexes the product, depending on config
     *
     * @param \Epicor\Comm\Model\Product $product
     */
    protected function _index()
    {
        try {
            // INDEX PRODUCTS
            $products = array_unique($this->_indexProducts);
            $indexproducts = $products;
            $productHelper = $this->commProductHelper->create();
            $productHelper->reindexProductById($indexproducts);
        } catch (\Exception $e) {
            $this->setStatusDescription('Indexing failed, please manually re-index' . $e->getMessage());
        }
    }

    private function updateEntityRegistrations($productId, $oldCategories, $newCategories)
    {
        $helper = $this->commEntityregHelper;
        /* @var $helper \Epicor\Comm\Helper\Entityreg */

        foreach ($oldCategories as $categoryId) {
            if (!in_array($categoryId, $newCategories)) {
                $helper->removeEntityRegistration($productId, 'CategoryProduct', $categoryId);
            }
        }

        foreach ($newCategories as $categoryId) {
            $helper->updateEntityRegistration($productId, 'CategoryProduct', $categoryId);
        }
    }

}
