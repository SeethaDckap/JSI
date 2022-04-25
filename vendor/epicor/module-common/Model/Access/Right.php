<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Access;


/**
 * 
 * Access right model
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 * @method string getEntityName()
 * @method string getType()
 * @method setEntityName(string $name)
 * @method setType(string $type)
 */
class Right extends \Epicor\Database\Model\Access\Right
{

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Group\Right\CollectionFactory
     */
    protected $commonResourceAccessGroupRightCollectionFactory;

    /**
     * @var \Epicor\Common\Model\Access\Group\RightFactory
     */
    protected $commonAccessGroupRightFactory;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Right\Element\CollectionFactory
     */
    protected $commonResourceAccessRightElementCollectionFactory;

    /**
     * @var \Epicor\Common\Model\Access\Right\ElementFactory
     */
    protected $commonAccessRightElementFactory;

    /**
     * @var \Epicor\Common\Model\Access\GroupFactory
     */
    protected $commonAccessGroupFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\ResourceModel\Access\Group\Right\CollectionFactory $commonResourceAccessGroupRightCollectionFactory,
        \Epicor\Common\Model\Access\Group\RightFactory $commonAccessGroupRightFactory,
        \Epicor\Common\Model\ResourceModel\Access\Right\Element\CollectionFactory $commonResourceAccessRightElementCollectionFactory,
        \Epicor\Common\Model\Access\Right\ElementFactory $commonAccessRightElementFactory,
        \Epicor\Common\Model\Access\GroupFactory $commonAccessGroupFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commonResourceAccessGroupRightCollectionFactory = $commonResourceAccessGroupRightCollectionFactory;
        $this->commonAccessGroupRightFactory = $commonAccessGroupRightFactory;
        $this->commonResourceAccessRightElementCollectionFactory = $commonResourceAccessRightElementCollectionFactory;
        $this->commonAccessRightElementFactory = $commonAccessRightElementFactory;
        $this->commonAccessGroupFactory = $commonAccessGroupFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Common\Model\ResourceModel\Access\Right');
    }

    /**
     * Returns an array of rights with labels and values
     * 
     * @return array
     */
    public function toOptionArray()
    {
        $arr = array();
        $collection = $this->getCollection();
        foreach ($collection->getItems() as $group) {
            $arr[] = array('label' => $group->getEntityName(), 'value' => $group->getEntityId());
        }
        return $arr;
    }

    /**
     * Deletes all elements and groups from the right before deletion
     */
    public function _beforeDelete()
    {
        $this->clearGroupsCache();
        $this->deleteLinkedGroups();
        $this->deleteLinkedElements();
        parent::_beforeDelete();
    }

    /**
     * Returns an array of group ids linked with this right
     * 
     * @return array
     */
    public function getLinkedGroups()
    {
        $collection = $this->commonResourceAccessGroupRightCollectionFactory->create();
        $collection->addFilter('right_id', $this->getId());
        return $collection->getItems();
    }

    /**
     * Deletes all groups linked with this right
     */
    public function deleteLinkedGroups()
    {
        $model = $this->commonAccessGroupRightFactory->create();
        $collection = $model->getCollection();
        $collection->addFilter('right_id', $this->getId());
        $items = $collection->getItems();
        //delete existing.
        foreach ($items as $group) {
            $group->delete();
        }
    }

    /**
     * Returns an array of element ids linked with this right
     * 
     * @return array
     */
    public function getLinkedElements()
    {
        $collection = $this->commonResourceAccessRightElementCollectionFactory->create();
        $collection->addFilter('right_id', $this->getId());
        return $collection->getItems();
    }

    /**
     * Deletes all elements linked with this right
     */
    public function deleteLinkedElements()
    {
        $model = $this->commonAccessRightElementFactory->create();
        $collection = $model->getCollection();
        $collection->addFilter('right_id', $this->getId());
        $items = $collection->getItems();
        //delete existing.
        foreach ($items as $element) {
            $element->delete();
        }
    }

    /**
     * Gets all groups for this right and clears the cache for it
     */
    public function clearGroupsCache()
    {
        $collection = $this->commonResourceAccessGroupRightCollectionFactory->create();
        $collection->addFilter('right_id', $this->getId());
        foreach ($collection->getItems() as $right) {
            $this->commonAccessGroupFactory->create()->clearCache($right->getGroupId());
        }
    }

}
