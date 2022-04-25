<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Message\Upload;

use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * Response STG - Upload Catalogue Record
 *
 * Send up details for a product group, used to create/amend/
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Stg extends \Epicor\Comm\Model\Message\Upload
{
    const XML_PATH_IGNORE_NULL_PARENT = 'epicor_comm_field_mapping/stg_mapping/ignore_null_parent';
    const XML_PATH_USE_DEFAULT        = 'epicor_comm_field_mapping/stg_mapping/use_default_for_name_desc';
    const ALL_STORE_VIEW_ID           = 0;

    protected $_maxDeadlockRetriesDefault = 5;
    private $_languageData = null;
    private $_parents;
    private $_storeParents = array();
    protected $_updatecategory = true;
    protected $_imagesProcessed = false;
    protected $_attrPaths = array(
        'name' => 'group_name_update',
        'description' => 'group_description_update',
        'image' => 'group_image_filename_update',
        'parent_id' => 'group_parent_update',
        'is_active' => 'group_active_update',
    );

    /**
     * @var array
     */
    private $useDefaultPaths = [
        CategoryInterface::KEY_NAME,
        CategoryInterface::KEY_IS_ACTIVE,
        CategoryInterface::KEY_INCLUDE_IN_MENU,
        'description',
    ];

    /**
     * @var boolean
     */
    private $useDefaultEnabled = 0;

    /**
     * @var \Epicor\Common\Model\Xmlvarien
     */
    private $_group = null;

    /**
     * @var \Epicor\Common\Model\Xmlvarien
     */
    private $_groupAttributes = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $catalogResourceModelCategoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $catalogApiCategoryRepositoryInterface;

    /**
     * PositionUsedCategoryData
     *
     * @var array
     */
    private $positionUsedCategoryData = [];

    /**
     * Position assigned
     *
     * @var boolean
     */
    private $assigned = false;

    /**
     * Weighting Val Used for position
     *
     * @var boolean
     */
    private $weightingValUsed = false;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $catalogResourceModelCategoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $catalogApiCategoryRepositoryInterface,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->catalogResourceModelCategoryCollectionFactory = $catalogResourceModelCategoryCollectionFactory;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->catalogApiCategoryRepositoryInterface = $catalogApiCategoryRepositoryInterface;
        $this->resourceConnection                    = $resourceConnection;
        parent::__construct($context, $resource, $resourceCollection, $data);

        $this->setConfigBase('epicor_comm_field_mapping/stg_mapping/');
        $this->setMessageType('STG');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_CATALOG);
        $this->setStatusCode(self::STATUS_SUCCESS);
        $this->setStatusDescription('');
        $this->registry->register('entity_register_update_category', true, true);

    }


    public function resetProcessFlags()
    {
        parent::resetProcessFlags();

        $this->_languageData = null;
        $this->_parents = null;
        $this->_storeParents = array();
        $this->_updatecategory = true;
    }

    public function beforeProcessAction()
    {
        parent::beforeProcessAction();
        //$this->_disableIndexing();
    }

    public function afterProcessAction()
    {
        //$this->_resetIndexing();
        //$this->_index();
        parent::afterProcessAction();
    }

    /**
     * Process a request
     *
     * @return
     */
    public function processAction()
    {
        $this->checkUseDefaultEnabled();

        $this->_group = $this->getRequest()->getGroup();

        if (!($this->_group instanceof \Epicor\Common\Model\Xmlvarien)) {
            throw new \Exception(
                $this->getErrorDescription(self::STATUS_XML_TAG_MISSING, 'group'), self::STATUS_XML_TAG_MISSING
            );
        }

        $this->_groupAttributes = $this->_group->getData('_attributes');

        $groupCode = $this->_group->getGroupCode();
        $this->setMessageSubject($groupCode);

        $this->_loadStores($this->_group, true);

        if ($this->useDefaultEnabled) {
            array_unshift($this->_stores, $this->storeManager->getStore(self::ALL_STORE_VIEW_ID));
        }

        if (!empty($groupCode)) {
            $this->_loadParents();

            $deleteFlag = ($this->_groupAttributes instanceof \Epicor\Common\Model\Xmlvarien) ? $this->_groupAttributes->getDelete() : '';

            $updateLanguages = $this->isUpdateable('group_languages_update', $this->_updatecategory);
            foreach ($this->_stores as $store) {
                $this->setStoreId($store->getId());
                if ($deleteFlag == 'Y') {
                    if ($this->isUpdateable('group_delete_update')) {
                        $this->_deleteCategory($groupCode);
                    }
                } else {
                    $this->_loadLanguageData();
                    $categories = $this->_loadStoreCategory($groupCode);
                    foreach ($categories as $category) {
                        if ($this->isUpdateable('group_weighting_update', $this->_updatecategory) && !empty($this->_group->getWeighting())) {
                            $this->assignPostion($category, $this->_group->getWeighting());
                        }

                        //wso-1296 - had to add this to allow delete of language via config option
                        $storeCode = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
                        $deleteLanguageFlag = 'N';
                        if (isset($this->_languageData[$storeCode])) {
                            $languageData = $this->_languageData[$storeCode];
                            $languageDataAtt = $languageData->getData('_attributes');
                            $deleteLanguageFlag = ($languageDataAtt instanceof \Epicor\Common\Model\Xmlvarien) ? $languageDataAtt->getDelete() : '';
                        }
                        if ($deleteLanguageFlag != 'Y' && ($updateLanguages && $this->_updatecategory)) {  // if not delete, not new and language update is allowed
                            //wso-1296
                            /* @var $category \Magento\Catalog\Model\Category */
                            foreach ($category->getAttributes() as $attribute) {
                                /* @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                                // Unset data if object attribute has no value in current
                                if ( array_key_exists($attribute->getAttributeCode(), $this->_attrPaths) &&
                                     $this->isUpdateable($this->_attrPaths[$attribute->getAttributeCode()], $this->_updatecategory)
                                ) {
                                    if (!$attribute->isStatic() && !$attribute->isScopeGlobal() && !$category->getExistsStoreValueFlag($attribute->getAttributeCode())) {
                                        $category->setData($attribute->getAttributeCode(), false);
                                    }
                                }
                            }
                        }

                        $this->combineVisibility($category); //wso-1296 moved this from after updateStoreCategory, as updates failing with missing sort attribute
                        if ($updateLanguages && $this->_updatecategory) {
                            if (isset($this->_languageData[$storeCode])) {
                                $this->updateStoreCategory($category, $this->_languageData[$storeCode], $this->getStoreId());
                            }
                        }
                        if ($this->useDefaultEnabled && $this->getStoreId()) {
                            foreach ($this->useDefaultPaths as $path) {
                                $category->setData($path, null);
                            }
                        }
                        // allow for empty array in erp images
                        $category->setEccErpImages(is_array($category->getEccErpImages()) ? $category->getEccErpImages() : unserialize($category->getEccErpImages()));
                        $category->setStoreId($this->getStoreId())->save();
                    }
                }
            }

        } else {
            if ($this->_group->hasVarienDataFromPath('group/group_code')) {
                throw new \Exception(
                    $this->getErrorDescription(self::STATUS_PRODUCT_GROUP_NOT_ON_FILE, $groupCode), self::STATUS_PRODUCT_GROUP_NOT_ON_FILE
                );
            } else {
                throw new \Exception(
                    $this->getErrorDescription(self::STATUS_XML_TAG_MISSING, 'groupCode'), self::STATUS_XML_TAG_MISSING
                );
            }
        }
    }

    /**
     * @param $id
     *
     * @return null|string
     */
    private function getCategoryName($id)
    {
        try {
            $categoryInstance = $this->catalogApiCategoryRepositoryInterface->get($id, self::ALL_STORE_VIEW_ID);
            return $categoryInstance->getName();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), self::STATUS_GENERAL_ERROR);
        }

    }//end getCategoryName()

    /**
     * Assign position based upon weighting tag or next available position.
     *
     * @param object  $category  Category Data.
     * @param integer $weighting Position value from weighting tag.
     *
     * @throws \Exception
     */
    private function assignPostion($category, $weighting)
    {
        $duplicatePosition = $this->checkForDuplicatePosition($weighting, $category);
        if (!$duplicatePosition) {
            $category->setPosition($weighting);
            $this->weightingValUsed = true;
        } else {
            if (!$this->assigned && !$this->weightingValUsed) {
                $path          = explode('/', (string) $category->getPath());
                $toUpdateChild = array_diff($path, [$category->getEntityId()]);
                $childPath     = implode('/', $toUpdateChild);
                $maxPosition   = $this->getMaxPositionByLevel($category->getEntityId(), $childPath);
                $category->setPosition($maxPosition + 1);
                $this->assigned = true;

                if (!empty($this->positionUsedCategoryData) && !$this->weightingValUsed) {
                    $used = $this->positionUsedByCurrentCategory($this->positionUsedCategoryData, $category->getEntityId());
                    if(!$used) {
                        $this->setStatusDescription(
                            $this->getWarningDescription(self::STATUS_WARNING).' : '.$this->getCategoryName(
                                $this->positionUsedCategoryData['entity_id']
                            ).' is already using weighting '.$this->positionUsedCategoryData['position'].', '.$category->getName(
                            ).' has been assigned weighting '.$category->getPosition().'.'
                        );
                    }
                }
            }
        }//end if

    }//end assignPostion()

    /**
     * @param $positionUsedCategoryData
     * @param $currentCategroyId
     *
     * @return int
     */
    private function positionUsedByCurrentCategory($positionUsedCategoryData, $currentCategroyId)
    {
        $used = 0;
        if ($currentCategroyId === $positionUsedCategoryData['entity_id']) {
            $used = 1;
        }

        return $used;

    }//end positionUsedByCurrentCategory()

    /**
     * Get maximum position of child categories by specific tree path.
     *
     * @param integer $id   Category id.
     * @param string  $path Category path.
     *
     * @return integer|string
     */
    protected function getMaxPositionByLevel($id, $path)
    {
        $connection = $this->resourceConnection->getConnection();
        $level      = count(explode('/', (string) $path));
        $bind       = [
            'c_level' => $level,
            'c_path'  => $path.'/%',
            'c_id'    => $id,
        ];
        $select     = $connection->select()->from(
            'catalog_category_entity',
            'MAX(position)'
        )->where(
            $connection->quoteIdentifier('path').' LIKE :c_path'
        )->where(
            $connection->quoteIdentifier('level').' = :c_level'
        )->where($connection->quoteIdentifier('entity_id').' != :c_id');
        $position   = $connection->fetchOne($select, $bind);
        if (!$position) {
            $position = 0;
        }

        return $position;

    }//end getMaxPositionByLevel()


    /**
     * Check Category position is already assigned for some other category in same level
     *
     * @param integer $weighting    Category position Value.
     * @param object  $categoryData Category object data.
     *
     * @return integer
     */
    private function checkForDuplicatePosition($weighting, $categoryData)
    {
        $path                    = explode('/', (string) $categoryData->getPath());
        $toUpdateChild           = array_diff($path, [ $categoryData->getEntityId()]);
        $childPath               = implode('/', $toUpdateChild);
        $duplicatePositionentry  = 0;
        $this->positionUsedCategoryData =[];
        $categoryPositionPresent = $this->catalogResourceModelCategoryCollectionFactory->create()
            ->addFieldToFilter('position', ['eq' => $weighting])
            ->addFieldToFilter('path', ['like' => $childPath.'/%'])
            ->addFieldToSelect('*')
            ->addFieldToFilter('level', ['eq' => $categoryData->getLevel()]);
        if (count($categoryPositionPresent->getData()) > 0) {
            $positionusedByCategory         = $categoryPositionPresent->getData();
            $this->positionUsedCategoryData = $positionusedByCategory[0];
            $duplicatePositionentry         = 1;
        }

        return $duplicatePositionentry;

    }//end checkForDuplicatePosition()


    /**
     * Deletes the category
     */
    private function _deleteCategory($groupCode, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->getStoreId();
        }
        $parentId = $this->createPath($storeId, true);
        $this->_storeParents[$storeId] = $parentId;

        $items = $this->_loadCategory($groupCode, $parentId);

        if (!empty($items)) {
            foreach ($items as $category) {
                $category->setStoreId(0)->delete();
            }
        }
    }

    /**
     * Updates / deletes a category using the data supplied in the message
     *
     * @param \Epicor\Common\Model\Xmlvarien $languageData
     * @param integer $storeId
     */
    private function updateStoreCategory(&$category, $languageData, $storeId)
    {
        $languageDataAtt = $languageData->getData('_attributes');
        $deleteLanguageFlag = ($languageDataAtt instanceof \Epicor\Common\Model\Xmlvarien) ? $languageDataAtt->getDelete() : '';
        if ($deleteLanguageFlag == 'Y') {
            if (!$storeId && $this->useDefaultEnabled) {
                return;
            }

            if ($this->isUpdateable('group_languages_language_delete_update')) {
                // Wipe out any previous values so that we can apply data from this message
                foreach ($category->getAttributes() as $attribute) {
                    /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
                    if (!$attribute->isStatic() && !$attribute->isScopeGlobal()) {
                        $category->setData($attribute->getAttributeCode(), false);
                    }
                }
            }
        } else {
            $this->combineData($languageData, $category);
            if ($this->isUpdateable('group_parent_update', true)) {
                $parentId = $this->createPath($storeId);
                if ($parentId != $category->getParentId()) {
                    $category->move($parentId, 0);
                }
            }

            $this->validateCategory($category);
        }
    }

    /**
     * Updates a category with data from the message
     *
     * @param Varien_object $erpData
     * @param \Magento\Catalog\Model\Category $group
     */
    private function combineData($erpData, &$group)
    {
        $this->combineDescriptions($erpData, $group);
        $this->combineImages($erpData, $group);

        return $group;
    }

    /**
     * Updates the category images
     *
     * @param Varien_object $erpData
     * @param \Magento\Catalog\Model\Category $group
     */
    private function combineImages($erpData, &$group)
    {
        if ($this->_imagesProcessed) {
            return true;
        }

        if ($this->isUpdateable('group_image_filename_update', $this->_updatecategory)) {
            $group->setEccErpImagesProcessed(1);

            $imagesGroup = $erpData->getImages();

            $images = ($imagesGroup instanceof \Epicor\Common\Model\Xmlvarien) ? $imagesGroup->getasarrayImage() : array();

            $groupImages = $group->getEccErpImages();
            if (!is_array($groupImages)) {
                $groupImages = array();
            }

            foreach ($images as $image) {
                $filename = strtolower($image->getFilename());
                $imageAtt = $image->getData('_attributes');
                $type = ($imageAtt instanceof \Epicor\Common\Model\Xmlvarien) ? $imageAtt->getType() : '';

                $types = array();
                if (strpos($type, 'L') !== false || strpos($type, 'S') !== false) {
                    $types[] = 'L';
                }

                if (strpos($type, 'T') !== false) {
                    $types[] = 'T';
                }

                $imageData = array(
                    'filename' => $filename,
                    'types' => $types,
                    'status' => 0
                );

                $attachment = $image->getAttachment();
                if ($attachment instanceof \Epicor\Common\Model\Xmlvarien) {
                    $imageData = array_merge($imageData, $attachment->getData());
                }

                if ($imageData['filename'] && !empty($this->_stores)) {
                    $data = $imageData;

                    foreach ($this->_stores as $store) {
                        $data['stores'][] = $store->getId();
                        $data['store_info'][$store->getId()] = $imageData;
                    }

                    $groupImages[$data['filename']] = $data;
                    $group->setEccErpImagesProcessed(0);
                }
            }

            $group->setEccErpImages($groupImages);
        }

        $this->_imagesProcessed = true;
    }

    /**
     * Updates the category visiblity settings
     *
     * @param Varien_object $erpData
     * @param \Magento\Catalog\Model\Category $group
     */
    private function combineVisibility(&$group, $includeInMenu = 1)
    {
        $useConfigFields = [];

        $visible = ($this->_groupAttributes instanceof \Epicor\Common\Model\Xmlvarien) ? $this->_groupAttributes->getVisible() : '';

        if ($this->isUpdateable('group_active_update', $this->_updatecategory)) {
            if (!empty($visible) && ($visible == 'Y') && $includeInMenu) {
                $group->setIsActive(1);
            } else {
                $group->setIsActive(0);
            }
        }

        if ($this->useDefaultEnabled && $this->getStoreId() == null) {
            $group->setIsActive(0);
            $includeInMenu = 0;
        }

        $group->setIncludeInMenu($includeInMenu);

        $available = $group->getData('available_sort_by');
        if (empty($available)) {
            //M1 > M2 Translation Begin (Rule 38)
            //$group->setAvailableSortBy(false);
            $useConfigFields[] = 'available_sort_by';
            //M1 > M2 Translation End
        }
        $default = $group->getData('default_sort_by');
        if (empty($default)) {
            //M1 > M2 Translation Begin (Rule 38)
            //$group->setDefaultSortBy(false);
            $useConfigFields[] = 'default_sort_by';
            //M1 > M2 Translation End
        }
        $group->setData('use_post_data_config', $useConfigFields);
    }

    /**
     * Updates the category descriptions
     *
     * @param \Epicor\Common\Model\Xmlvarien $erpData
     * @param \Magento\Catalog\Model\Category $group
     */
    private function combineDescriptions($erpData, &$group)
    {

        $code = $erpData->getLanguageCode();
        $shortDesc = $erpData->getName();

        if ($this->isUpdateable('group_name_update', $this->_updatecategory)) {
            if (!empty($shortDesc)) {
                $group->setName($shortDesc);
            } else {
                throw new \Exception('Missing Group Name for language ' . $code, self::STATUS_GENERAL_ERROR);
            }
        }

        $fullDesc = $erpData->getDescription();

        if ($this->isUpdateable('group_description_update', $this->_updatecategory)) {
            if (!empty($fullDesc)) {
                $group->setDescription($fullDesc);
            }
        }
    }

    /**
     * Loads / creates entire path in Magento tree structure for the given store id
     *
     * @param integer $storeId
     * @param boolean $delete - whether this is being used to find the parent for a delete
     *
     * @return integer
     */
    private function createPath($storeId, $delete = false)
    {

        // set to root category of the store
        if ($this->useDefaultEnabled && !$storeId) {
            $storeId = $this->storeManager->getDefaultStoreView()->getId();
        }

        $rootId = $this->storeManager->getStore($storeId)->getRootCategoryId();
        $groupId = null;
        $lowestLevel = 0;
        $parents = array(
            0 => $rootId
        );

        if (!empty($this->_parents)) {

            foreach ($this->_parents as $parent) {

                $groupCode = $parent->getGroupCode();

                $parentAtt = $parent->getData('_attributes');
                $groupLevel = ($parentAtt instanceof \Epicor\Common\Model\Xmlvarien) ? $parentAtt->getLevel() : '';

                $catParent = (isset($parents[$groupLevel - 1])) ? $parents[$groupLevel - 1] : $rootId;

                $items = $this->_loadCategory($groupCode, $catParent);

                if (empty($items)) {

                    if ($delete) {
                        // throw an exception because path should exist
                        throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_PRODUCT_GROUP_PARENTS, 'Parent ' . $groupCode . ' not found'), self::STATUS_INVALID_PRODUCT_GROUP_PARENTS);
                    }
                    // group code not found
                    // create it with some default values
                    //M1 > M2 Translation Begin (Rule 8)
                    /*$groupData = array(
                       'name' => $parent->getName() ? : $groupCode,
                        'description' => $parent->getDescription(),
                       'is_active' => false,
                       'available_sort_by' => false,
                       'default_sort_by' => false,
                        'erp_code' => $groupCode,
                       'include_in_menu' => (!empty($groupLevel) && ($groupLevel > 3)) ? false : true,
                   );*/
                    $groupData = [
                        "parent_id" => $catParent,
                        'name' => $parent->getName() ?: $groupCode,
                        "is_active" => true,
                        'available_sort_by' => ['name'],
                        'default_sort_by' => 'name',
                        "include_in_menu" => (!empty($groupLevel) && ($groupLevel > 3)) ? false : true
                    ];
                    $customAttribute = [
                        'description' => $parent->getDescription(),
                        'ecc_erp_code' => $groupCode,
                    ];

                    try {
                        //$parentId = Mage::getModel('catalog/category_api')
                        //->create($catParent, $groupData, 0);
                        $category = $this->catalogCategoryFactory->create();
                        $category->setStoreId($storeId);

                        $category->addData($groupData);

                        if (!$category->getId()) {
                            $parentCategory = $this->catalogCategoryFactory->create()->load($catParent);
                            $category->setPath($parentCategory->getPath());
                            $category->setParentId($parentCategory->getId());
                        }
                        $category->setCustomAttributes($customAttribute);
                        // $repository = $this->catalogApiCategoryRepositoryInterface;
                        //  $result = $repository->save($category);
                        $category->save();
                        $parentId = $category->getId();
                        //M1 > M2 Translation End
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage(), self::STATUS_GENERAL_ERROR);
                    }
                } else {
                    foreach ($items as $item) {
                        $parentId = $item->getId();
                    }
                }

                $parents[$groupLevel] = $parentId;

                if ($groupLevel >= $lowestLevel) {
                    $lowestLevel = $groupLevel;
                    $groupId = $parentId;
                }
            }
        }

        if (empty($groupId)) {
            $groupId = $rootId;
        }

        return $groupId;
    }

    /**
     * Validates the category
     *
     * @param \Magento\Catalog\model\Category $category
     * @throws \Exception
     */
    private function validateCategory($category)
    {
        $validate = $category->validate();
        if ($validate !== true) {
            foreach ($validate as $code => $error) {
                if ($error === true) {
                    throw new \Exception("Attribute \"$code\" is required.", self::STATUS_GENERAL_ERROR);
                } else {
                    throw new \Exception($error, self::STATUS_GENERAL_ERROR);
                }
            }
        }
    }

    /**
     * Processes the languages tag into an array grouped by language code
     */
    private function _loadLanguageData()
    {

        if (is_null($this->_languageData)) {

            $this->_languageData = array();

            $languagesGroup = $this->_group->getLanguages();
            $languages = ($languagesGroup) ? $languagesGroup->getasarrayLanguage() : array();

            if (empty($languages)) {
                throw new \Exception('No languages provided', self::STATUS_GENERAL_ERROR);
            }

            $helper = $this->getHelper();

            foreach ($languages as $language) {
                $language_codes = $helper->getLanguageMapping($language->getLanguageCode(), $helper::ERP_TO_MAGENTO);

                foreach ($language_codes as $language_code) {
                    $language->setLanguageCode($language_code);
                    $this->_languageData[$language_code] = $language;
                }
            }

            if (empty($this->_languageData)) {
                throw new \Exception('Languages provided do not match any stores in the system', self::STATUS_GENERAL_ERROR);
            }
        }
    }

    /**
     * Loads the parents tag and processes it
     *
     * @throws \Exception
     */
    private function _loadParents()
    {

        $this->_parents = array();

        $parentDataGroup = $this->_group->getParents();
        $parentData = ($parentDataGroup) ? $parentDataGroup->getasarrayParent() : array();

        if (!empty($parentData)) {
            $levels = array();
            $duplicate = false;

            foreach ($parentData as $key => $value) {
                $valueAtt = $value->getData('_attributes');
                $level = ($valueAtt instanceof \Epicor\Common\Model\Xmlvarien) ? $valueAtt->getLevel() : '';
                $sortedParentData[$level] = $value;
            }

            ksort($sortedParentData);

            foreach ($sortedParentData as $parent) {

                $parentAtt = $parent->getData('_attributes');
                $groupLevel = ($parentAtt instanceof \Epicor\Common\Model\Xmlvarien) ? $parentAtt->getLevel() : '';

                if (!isset($this->_parents[$groupLevel])) {
                    $this->_parents[$groupLevel] = $parent;
                } else {
                    $duplicate = true;
                }

                $levels[] = $groupLevel;
            }

            if ($duplicate) {
                throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_PRODUCT_GROUP_PARENTS, 'Duplicate levels found'), self::STATUS_INVALID_PRODUCT_GROUP_PARENTS);
            }

            asort($levels);

            $nextLevel = 1;
            $levelsOk = true;
            foreach ($levels as $level) {
                if ($level != $nextLevel) {
                    $levelsOk = false;
                }
                $nextLevel++;
            }

            if (!$levelsOk) {
                throw new \Exception($this->getErrorDescription(self::STATUS_INVALID_PRODUCT_GROUP_PARENTS, 'Missing one or more sequential level'), self::STATUS_INVALID_PRODUCT_GROUP_PARENTS);
            }
        }
    }

    /**
     * Loads a category froms it's group code and store Id
     *
     * Note: a category can be in more than one place in the heirarchy, hence why an array is returned
     *
     * @param string $groupCode
     * @param integer $storeId
     *
     * @return array
     */
    private function _loadStoreCategory($groupCode, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->getStoreId();
        }
        // load a collection of categories that match the group code and store id
        $parentId = $this->createPath($storeId);
        $this->_storeParents[$storeId] = $parentId;

        $categories = $this->_loadCategory($groupCode, $parentId);
        if (empty($categories)) {
            $this->_updatecategory = false;
            // category needs creating
            $category = $this->_createRootCategory($groupCode, $parentId);
            $categories = array($category);
        }

        return $categories;
    }

    /**
     * Loads a category froms it's group code and store Id
     *
     * Note: a category can be in more than one place in the heirarchy, hence why an array is returned
     *
     * @param string $groupCode
     * @param integer $parentId
     *
     * @return array
     */
    private function _loadCategory($groupCode, $parentId)
    {
        // load a collection of categories that match the group code and parent id

        $erpCategories = $this->catalogResourceModelCategoryCollectionFactory->create()
                                                                             ->addFieldToFilter('ecc_erp_code', array('eq' => $groupCode))
                                                                             ->setStore($this->getStoreId())
                                                                             ->addAttributeToSelect('*');
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_IGNORE_NULL_PARENT)
            || !empty($this->_parents)
        ) {
            $erpCategories->addFieldToFilter('parent_id', array('eq' => $parentId));
        } else {
            foreach ($erpCategories as $key => $cat) {
                $paths = $cat->getPath();
                $path = explode('/', $paths);
                if (!in_array($parentId, $path)) {
                    $erpCategories->removeItemByKey($cat->getId());
                }

            }
        }
        return $erpCategories->getItems();
    }

    /**
     * Creates a category in the root store, with the given parent
     *
     * @param string $groupCode
     * @param integer $parentId
     *
     * @return \Epicor\Comm\Model\Category
     */
    private function _createRootCategory($groupCode, $parentId)
    {
        $defaultLanguage = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 0);
        if (in_array($defaultLanguage, $this->_languageData)) {
            $languageData = $this->_languageData[$defaultLanguage];
        } else {
            $languageData = reset($this->_languageData);
        }

        $parent_category = $this->catalogCategoryFactory->create()->setStoreId(0)->load($parentId);

        $category = $this->catalogCategoryFactory->create();
        $category->setEccErpCode($groupCode);
        $category->setAttributeSetId($category->getDefaultAttributeSetId());
        $category->setPath($parent_category->getPath());
        //M1 > M2 Translation Begin (Rule 38)
        $category->setAvailableSortBy(false);
        $category->setDefaultSortBy(false);
        //M1 > M2 Translation End
        $this->combineData($languageData, $category);
        $this->combineVisibility($category, 0);

        $this->validateCategory($category);

        $isAnchor = $this->scopeConfig->isSetFlag('epicor_comm_field_mapping/stg_mapping/new_categories_is_anchor_value', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $isAnchor ? $category->setIsAnchor(true) : $category->setIsAnchor(false);
        $category->save();

        return $category;
    }


    /**
     * Check use default is enabled for Name and Description.
     *
     * @return void
     */
    private function checkUseDefaultEnabled()
    {
        $this->useDefaultEnabled = $this->scopeConfig->isSetFlag(self::XML_PATH_USE_DEFAULT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    }//end checkUseDefaultEnabled()


}
