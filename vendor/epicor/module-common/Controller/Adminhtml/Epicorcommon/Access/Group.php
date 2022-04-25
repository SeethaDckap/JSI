<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access;


/**
 * 
 * Access Groups controller
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
abstract class Group extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    protected $_aclId = 'customer/access/groups';

    /**
     * @var \Epicor\Common\Model\Access\GroupFactory
     */
    protected $commonAccessGroupFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Epicor\Common\Model\Access\Group\CustomerFactory
     */
    protected $commonAccessGroupCustomerFactory;

    /**
     * @var \Epicor\Common\Model\Access\Group\RightFactory
     */
    protected $commonAccessGroupRightFactory;

    public function __construct(
        \Epicor\Common\Model\Access\GroupFactory $commonAccessGroupFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Epicor\Common\Model\Access\Group\RightFactory $commonAccessGroupRightFactory
    ) {
        $this->commonAccessGroupFactory = $commonAccessGroupFactory;
        $this->backendSession = $backendSession;
        $this->registry = $registry;
        $this->backendJsHelper = $backendJsHelper;
        $this->commonAccessGroupCustomerFactory = $commonAccessGroupCustomerFactory;
        $this->commonAccessGroupRightFactory = $commonAccessGroupRightFactory;
    }
    protected function _initPage()
    {
        $this->loadLayout()
            ->_setActiveMenu('epicor_common/access/group')
            ->_addBreadcrumb(__('Access Group'), __('Access Group'));
        return $this;
    }
private function _loadGroup($id)
    {

        $model = $this->commonAccessGroupFactory->create();
        /* @var $model Epicor_Common_Model_Access_Group */

        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $data = $this->backendSession->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->backendSession->addError(__('Access Group does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->registry->register('access_group_data', $model);
    }
/**
     * 
     * @param array $customerIds
     * @param \Epicor\Common\Model\Access\Group $group
     */
    private function saveCustomers($data, $group)
    {

        $customerIds = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['customers']));

        $model = $this->commonAccessGroupCustomerFactory->create();
        /* @var $model Epicor_Common_Model_Access_Group_Customer */
        $collection = $model->getCollection();
        $collection->addFilter('group_id', $group->getId());
        $items = $collection->getItems();

        $existing = array();

        // Remove old - only if they're not passed in the data

        foreach ($items as $cus) {
            if (!in_array($cus->getCustomerId(), $customerIds)) {
                $cus->delete();
            } else {
                $existing[] = $cus->getCustomerId();
            }
        }

        // Add new - only if they don't already exist

        foreach ($customerIds as $customerId) {
            if (!in_array($customerId, $existing)) {
                $model = $this->commonAccessGroupCustomerFactory->create();
                /* @var $model Epicor_Common_Model_Access_Group_Customer */
                $model->setCustomerId($customerId);
                $model->setGroupId($group->getId());
                $model->save();
            }
        }
    }
/**
     * 
     * @param array $rightIds
     * @param \Epicor\Common\Model\Access\Group $group
     */
    private function saveRights($data, $group)
    {
        $rightIds = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['links']['rights']));

        $existing = array();

        $model = $this->commonAccessGroupRightFactory->create();
        /* @var $model Epicor_Common_Model_Access_Group_Right */
        $collection = $model->getCollection();
        $collection->addFilter('group_id', $group->getId());
        $items = $collection->getItems();

        // Remove old - only if they're not passed in the data

        foreach ($items as $right) {
            if (!in_array($right->getRightId(), $rightIds)) {
                $right->delete();
            } else {
                $existing[] = $right->getRightId();
            }
        }

        // Add new - only if they don't already exist

        foreach ($rightIds as $rightId) {
            if (!in_array($rightId, $existing)) {
                $model = $this->commonAccessGroupRightFactory->create();
                /* @var $model Epicor_Common_Model_Access_Group_Right */
                $model->setRightId($rightId);
                $model->setGroupId($group->getId());
                $model->save();
            }
        }

        $group->clearCache();
    }

}
