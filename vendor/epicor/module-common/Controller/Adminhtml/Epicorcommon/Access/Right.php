<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access;


/**
 * 
 * Access rights controller
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
abstract class Right extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    private $_selected = array();
    protected $_aclId = 'customer/access/rights';

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Epicor\Common\Model\Access\Group\RightFactory
     */
    protected $commonAccessGroupRightFactory;

    /**
     * @var \Epicor\Common\Model\Access\GroupFactory
     */
    protected $commonAccessGroupFactory;

    /**
     * @var \Epicor\Common\Model\Access\Right\ElementFactory
     */
    protected $commonAccessRightElementFactory;

    /**
     * @var \Epicor\Common\Model\Access\RightFactory
     */
    protected $commonAccessRightFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Common\Model\Access\Group\RightFactory $commonAccessGroupRightFactory,
        \Epicor\Common\Model\Access\GroupFactory $commonAccessGroupFactory,
        \Epicor\Common\Model\Access\Right\ElementFactory $commonAccessRightElementFactory,
        \Epicor\Common\Model\Access\RightFactory $commonAccessRightFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Registry $registry
    ) {
        $this->backendJsHelper = $backendJsHelper;
        $this->commonAccessGroupRightFactory = $commonAccessGroupRightFactory;
        $this->commonAccessGroupFactory = $commonAccessGroupFactory;
        $this->commonAccessRightElementFactory = $commonAccessRightElementFactory;
        $this->commonAccessRightFactory = $commonAccessRightFactory;
        $this->backendSession = $backendSession;
        $this->registry = $registry;
    }
    protected function _initPage()
    {
        $this->loadLayout()
            ->_setActiveMenu('epicor_common/access/right')
            ->_addBreadcrumb(__('Access Right'), __('Access Right'));
        return $this;
    }
private function saveGroups($data, $right)
    {
        $groupIds = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['groups']));

        // Remove old - only if they're not passed in the data

        $model = $this->commonAccessGroupRightFactory->create();
        $collection = $model->getCollection();
        $collection->addFilter('right_id', $right->getId());
        $items = $collection->getItems();
        //delete existing.
        foreach ($items as $group) {
            if (!in_array($group->getGroupId(), $groupIds)) {
                $group->delete();
            } else {
                $existing[] = $group->getGroupId();
            }
        }

        // Add new - only if they don't already exist

        foreach ($groupIds as $groupId) {
            if (!in_array($groupId, $existing)) {
                $model = $this->commonAccessGroupRightFactory->create();
                $model->setRightId($right->getId());
                $model->setGroupId($groupId);
                $model->save();
                $this->commonAccessGroupFactory->create()->clearCache($groupId);
            }
        }
    }

    /**
     * @param array $elementIds
     * @param \Epicor\Common\Model\Access\Right $right
     */
    private function saveElements($data, $right)
    {
        $elementIds = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['elements']));

        // Remove old - only if they're not passed in the data
        $existing = array();

        $model = $this->commonAccessRightElementFactory->create();
        $collection = $model->getCollection();
        $collection->addFilter('right_id', $right->getId());
        $items = $collection->getItems();
        //delete existing.
        foreach ($items as $element) {
            if (!in_array($element->getElementId(), $elementIds)) {
                $element->delete();
            } else {
                $existing[] = $element->getElementId();
            }
        }

        // Add new - only if they don't already exist

        foreach ($elementIds as $elementId) {
            if (!in_array($elementId, $existing)) {
                $model = $this->commonAccessRightElementFactory->create();
                $model->setRightId($right->getId());
                $model->setElementId($elementId);
                $model->save();
            }
        }

        $right->clearGroupsCache();
    }
private function _loadRight($id)
    {

        $model = $this->commonAccessRightFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $data = $this->backendSession->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->backendSession->addError(__('Access Right does not exist'));
                $this->_redirect('*/*/');
            }
        }

        $this->registry->register('access_right_data', $model);
    }
}
