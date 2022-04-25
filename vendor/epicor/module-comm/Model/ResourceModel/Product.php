<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\ResourceModel;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Product
 *
 * @author David.Wylie
 */
class Product extends \Magento\Catalog\Model\ResourceModel\Product
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\CategoryFactory
     */
    private $categoryFactory;

    /* update default category position check
    * @var boolean
    */
    private $updateDefaultCategoryPosition;

    const XML_PATH_PRODUCT_POSITION_IN_CATEGORY =  'epicor_comm_field_mapping/sgp_mapping/product_position_in_category';


    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category $catalogCategory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        \Magento\Eav\Model\Entity\TypeFactory $typeFactory,
        \Magento\Catalog\Model\Product\Attribute\DefaultAttributes $defaultAttributes,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        $data = []
    )
    {
        $this->categoryFactory = $categoryFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $context,
            $storeManager,
            $modelFactory,
            $categoryCollectionFactory,
            $catalogCategory,
            $eventManager,
            $setFactory,
            $typeFactory,
            $defaultAttributes,
            $data
        );
    }
    /**
     * Save product category relations
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Catalog\Model\ResourceModel\Product
     */
    protected function _saveCategories(\Magento\Framework\DataObject $object)
    {
        /**
         * If category ids data is not declared we haven't do manipulations
         */
        if (!$object->hasCategoryIds()) {
            return $this;
        }
        $categoryIds = $object->getCategoryIds();
        $oldCategoryIds = $this->getCategoryIds($object);

        $object->setIsChangedCategories(false);

        if ($this->scopeConfig->isSetFlag('Epicor_Comm/group_length/apply_default_position', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $update = $categoryIds;
        } else {
            $update = array_diff($categoryIds, $oldCategoryIds);
        }
        $delete = array_diff($oldCategoryIds, $categoryIds);

        $write = $this->getConnection();
        if (!empty($update)) {
            $data = array();
            foreach ($update as $categoryId) {
                if (empty($categoryId)) {
                    continue;
                }
                $data[] = $this->setProductCategoryData($categoryId, $object);
            }
            if ($data) {
                $write->insertOnDuplicate($this->getProductCategoryTable(), $data);
            }
        }

        if (!empty($delete)) {
            foreach ($delete as $categoryId) {
                $where = array(
                    'product_id = ?' => (int) $object->getId(),
                    'category_id = ?' => (int) $categoryId,
                );

                $write->delete($this->getProductCategoryTable(), $where);
            }
        }

        if (!empty($update) || !empty($delete)) {
            $object->setAffectedCategoryIds(array_merge($update, $delete));
            $object->setIsChangedCategories(true);
        }

        return $this;
    }

    private function setProductCategoryData($categoryId, $object)
    {
        if (!$this->updateDefaultCategoryPosition) {
        $this->updateDefaultCategoryPosition =
            $this->scopeConfig->isSetFlag(self::XML_PATH_PRODUCT_POSITION_IN_CATEGORY);
    }
        $categoryPositionData = array(
            'category_id' => (int)$categoryId,
            'product_id' => (int)$object->getId()
        );
        $category = $this->categoryFactory->create()->load($categoryId);
        $categoryProductsPosition = $category->getProductsPosition() ? $category->getProductsPosition() : [];
        $originalData = $object->getOrigData();
        $preexistingProductCategories = isset($originalData['category_ids']) ? $originalData['category_ids'] : [];
        $eccDefaultCategoryPosition = $object->getEccDefaultCategoryPosition();
        // if product already exists in category, apply check if default category position is to be applied
        // if not to update, replace with original value
        if (!$this->updateDefaultCategoryPosition) {
            if (isset($preexistingProductCategories) && in_array($categoryId, $preexistingProductCategories)) {
                if (isset($categoryProductsPosition[$object->getId()])) {
                    $eccDefaultCategoryPosition = $categoryProductsPosition[$object->getId()];
                }
            }
        }

        //apply config position check
        if ($eccDefaultCategoryPosition) {
            $categoryPositionData['position'] = $eccDefaultCategoryPosition;
        }

        return $categoryPositionData;
    }
}
