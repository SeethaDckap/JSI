<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper;


class Setup
{

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\ElementFactory
     */
    protected $commonResourceAccessElementFactory;

    /**
     * @var \Epicor\Common\Model\Access\ElementFactory
     */
    protected $commonAccessElementFactory;

    /**
     * @var \Epicor\Common\Model\Access\RightFactory
     */
    protected $commonAccessRightFactory;

    /**
     * @var \Epicor\Common\Model\Access\GroupFactory
     */
    protected $commonAccessGroupFactory;

    /**
     * @var \Epicor\Common\Model\Access\Group\RightFactory
     */
    protected $commonAccessGroupRightFactory;

    /**
     * @var \Epicor\Common\Model\Access\Right\ElementFactory
     */
    protected $commonAccessRightElementFactory;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Right\Element\CollectionFactory
     */
    protected $commonResourceAccessRightElementCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    public function __construct(
        \Epicor\Common\Model\ResourceModel\Access\ElementFactory $commonResourceAccessElementFactory,
        \Epicor\Common\Model\Access\ElementFactory $commonAccessElementFactory,
        \Epicor\Common\Model\Access\RightFactory $commonAccessRightFactory,
        \Epicor\Common\Model\Access\GroupFactory $commonAccessGroupFactory,
        \Epicor\Common\Model\Access\Group\RightFactory $commonAccessGroupRightFactory,
        \Epicor\Common\Model\Access\Right\ElementFactory $commonAccessRightElementFactory,
        \Epicor\Common\Model\ResourceModel\Access\Right\Element\CollectionFactory $commonResourceAccessRightElementCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->commonResourceAccessElementFactory = $commonResourceAccessElementFactory;
        $this->commonAccessElementFactory = $commonAccessElementFactory;
        $this->commonAccessRightFactory = $commonAccessRightFactory;
        $this->commonAccessGroupFactory = $commonAccessGroupFactory;
        $this->commonAccessGroupRightFactory = $commonAccessGroupRightFactory;
        $this->commonAccessRightElementFactory = $commonAccessRightElementFactory;
        $this->commonResourceAccessRightElementCollectionFactory = $commonResourceAccessRightElementCollectionFactory;
        $this->eavConfig = $eavConfig;
    }
    /**
     * Migrates Data
     * 
     * @param string $to
     * @param string $from
     * @param Varien_Db_Adapter_Pdo_Mysql $conn
     * 
     * @return void
     */
    public function migrateData($to, $from, &$conn)
    {
        $data = $conn->describeTable($to);
        $select_data = '`' . implode('`, `', array_keys($data)) . '`';

        $sql = "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "TRUNCATE TABLE $to;\n";
        $sql .= "INSERT INTO $to (SELECT $select_data from $from);\n";
        $conn->exec($sql);
    }

    /**
     * Adds an excluded element to the access elements table
     * 
     * @param string $module
     */
    public function regenerateModuleElements($module = null)
    {
        $model = $this->commonResourceAccessElementFactory->create();
        /* @var $model Epicor_Common_Model_Resource_Access_Element */
        $model->regenerate($module);
    }

    /**
     * Adds an excluded element to the access elements table
     * 
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param string $block
     * @param string $action_type
     * @param int $excluded
     * @param in $portal
     */
    public function addAccessElement($module, $controller, $action, $block, $action_type, $excluded = 0, $portal = 0)
    {

        $elementMod = $this->commonResourceAccessElementFactory->create();
        /* @var $element Epicor_Common_Model_Resource_Access_Element */

        $model = $elementMod->loadByAll($module, $controller, $action, $block, $action_type);
        /* @var $element Epicor_Common_Model_Access_Element */

        if (empty($model) || !$model->getId()) {
            $model = $this->commonAccessElementFactory->create();
            /* @var $model Epicor_Common_Model_Access_Element */
            $model->setModule($module);
            $model->setController($controller);
            $model->setAction($action);
            $model->setBlock($block);
            $model->setActionType($action_type);
        }

        $model->setPortalExcluded($portal);
        $model->setExcluded($excluded);
        $model->save();

        return $model;
    }

    /**
     * Loads an access element
     * 
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param string $block
     * @param string $action_type
     */
    public function loadAccessElement($module, $controller, $action, $block, $action_type)
    {

        $elementMod = $this->commonResourceAccessElementFactory->create();
        /* @var $elementMod Epicor_Common_Model_Resource_Access_Element */

        return $elementMod->loadByAll($module, $controller, $action, $block, $action_type);
    }

    /**
     * Adds an excluded element to the access elements table
     * 
     * @param string $rightName
     * 
     * @return $model Epicor_Common_Model_Access_Right
     */
    public function addAccessRight($rightName)
    {
        $model = $this->loadAccessRightByName($rightName);
        if ($model->isObjectNew()) {
            $model = $this->commonAccessRightFactory->create();
            /* @var $model Epicor_Common_Model_Access_Right */
            $model->setEntityName($rightName);
            $model->save();
        }

        return $model;
    }

    /**
     * Gets an access Right by name
     * 
     * @param string $rightName
     * 
     * @return $model Epicor_Common_Model_Access_Group
     */
    public function loadAccessRightByName($rightName)
    {
        $model = $this->commonAccessRightFactory->create()->load($rightName, 'entity_name');
        /* @var $model Epicor_Common_Model_Access_Right */

        return $model;
    }

    /**
     * Adds an access group
     * 
     * @param string $groupName
     * 
     * @return $model Epicor_Common_Model_Access_Group
     */
    public function addAccessGroup($groupName)
    {
        $model = $this->loadAccessGroupByName($groupName);
        if ($model->isObjectNew()) {
            $model = $this->commonAccessGroupFactory->create();
            /* @var $model Epicor_Common_Model_Access_Group */
            $model->setEntityName($groupName);
            $model->save();
        }

        return $model;
    }

    /**
     * Gets an access group by name
     * 
     * @param string $groupName
     * 
     * @return $model Epicor_Common_Model_Access_Group
     */
    public function loadAccessGroupByName($groupName)
    {
        $model = $this->commonAccessGroupFactory->create()->load($groupName, 'entity_name');
        /* @var $model Epicor_Common_Model_Access_Group */

        return $model;
    }

    /**
     * Adds an access right to an access group
     * 
     * @param integer $groupId
     * @param integer $rightId
     * 
     * @return $model Epicor_Common_Model_Access_Group_Right
     */
    public function addAccessGroupRight($groupId, $rightId)
    {
        $model = $this->commonAccessGroupRightFactory->create();
        /* @var $model Epicor_Common_Model_Access_Group_Right */
        $model->setGroupId($groupId);
        $model->setRightId($rightId);
        $model->save();

        return $model;
    }

    /**
     * Adds an element to an access right
     * 
     * @param integer $rightId
     * @param integer $elementId
     * 
     * @return $model Epicor_Common_Model_Access_Group_Element
     */
    public function addAccessRightElement($rightId, $module, $controller, $action, $block, $action_type)
    {

        $elementMod = $this->commonResourceAccessElementFactory->create();
        /* @var $element Epicor_Common_Model_Resource_Access_Element */

        $element = $elementMod->loadByAll($module, $controller, $action, $block, $action_type);
        /* @var $element Epicor_Common_Model_Access_Element */

        $model = false;

        if (!empty($element) && $element->getId()) {
            $model = $this->commonAccessRightElementFactory->create();
            /* @var $model Epicor_Common_Model_Access_Right_Element */
            $model->setRightId($rightId);
            $model->setElementId($element->getId());
            $model->save();
        }

        return $model;
    }

    /**
     * Adds an element to an access right
     * 
     * @param integer $rightId
     * @param integer $elementId
     * 
     * @return $model Epicor_Common_Model_Access_Group_Element
     */
    public function addAccessRightElementById($rightId, $elementId)
    {
        $model = $this->commonAccessRightElementFactory->create();
        /* @var $model Epicor_Common_Model_Access_Right_Element */
        $model->setRightId($rightId);
        $model->setElementId($elementId);
        $model->save();

        return $model;
    }

    /**
     * Removes an access element from all rights it's associated with
     * 
     * @param integer $elementId 
     */
    public function removeElementFromRights($elementId)
    {
        $collection = $this->commonResourceAccessRightElementCollectionFactory->create();
        /* @var $collection Epicor_Common_Model_Resource_Access_Right_Element_Collection */
        $collection->addFilter('element_id', $elementId);

        foreach ($collection->getItems() as $element) {
            $element->delete();
        }
    }

    /**
     * Adds a Customer EAV Attribute
     * 
     * @param \Magento\Framework\Setup\SetupInterface $installer
     * @param string $name
     * @param array $definition
     */
    public function addCustomerAttribute($installer, $name, $definition)
    {
        $installer->addAttribute(
            'customer', $name, $definition
        );

        $entityTypeId = $installer->getEntityTypeId('customer');
        $attributeSetId = $installer->getDefaultAttributeSetId($entityTypeId);
        $attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

        $installer->addAttributeToGroup(
            $entityTypeId, $attributeSetId, $attributeGroupId, $name, '999'  //sort_order
        );

        $oAttribute = $this->eavConfig->getAttribute('customer', $name);
        $oAttribute->setData('used_in_forms', array('adminhtml_customer'));
        $oAttribute->save();
    }

    /**
     * Adds a column to an existing table, checking it's not there first
     * 
     * @param Varien_Db_Adapter_Pdo_Mysql $conn
     * @param string $tableName
     * @param string $columnName
     * @param array $definition
     */
    public function addTableColumn($conn, $tableName, $columnName, $definition)
    {
        if (!$conn->tableColumnExists($tableName, $columnName)) {
            $conn->addColumn($tableName, $columnName, $definition);
        }
    }

    /**
     * Sets visibility of attribute in form
     */
    public function attributeVisibilityInForm($name)
    {
        $oAttribute = $this->eavConfig->getAttribute('customer', $name);
        $oAttribute->setData('used_in_forms', array());
        $oAttribute->save();
    }

}
