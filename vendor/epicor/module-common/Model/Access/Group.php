<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Access;


/**
 * 
 * Access group model
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 * @method string getEntityName()
 * @method string getErpAccountId()
 * @method string getType()
 * @method setEntityName(string $name)
 * @method setErpAccountId(integer $erpAccountId)
 * @method setType(string $type)
 */
class Group extends \Epicor\Database\Model\Access\Group
{

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Group\Customer\CollectionFactory
     */
    protected $commonResourceAccessGroupCustomerCollectionFactory;

    /**
     * @var \Epicor\Common\Model\ResourceModel\Access\Group\Right\CollectionFactory
     */
    protected $commonResourceAccessGroupRightCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Common\Model\Access\Group\CustomerFactory
     */
    protected $commonAccessGroupCustomerFactory;

    /**
     * @var \Epicor\Common\Model\Access\Group\RightFactory
     */
    protected $commonAccessGroupRightFactory;

    private $_cacheState;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Epicor\Common\Model\ResourceModel\Access\Group\Customer\CollectionFactory $commonResourceAccessGroupCustomerCollectionFactory,
        \Epicor\Common\Model\ResourceModel\Access\Group\Right\CollectionFactory $commonResourceAccessGroupRightCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Epicor\Common\Model\Access\Group\RightFactory $commonAccessGroupRightFactory,
        \Magento\Framework\App\Cache\StateInterface $state,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commonResourceAccessGroupCustomerCollectionFactory = $commonResourceAccessGroupCustomerCollectionFactory;
        $this->commonResourceAccessGroupRightCollectionFactory = $commonResourceAccessGroupRightCollectionFactory;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->commonAccessGroupCustomerFactory = $commonAccessGroupCustomerFactory;
        $this->commonAccessGroupRightFactory = $commonAccessGroupRightFactory;
        $this->_cacheState = $state;
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
        $this->_init('Epicor\Common\Model\ResourceModel\Access\Group');
    }

    /**
     * Gets an array of all groups
     * 
     * @return array
     */
    public function toOptionArray()
    {
        $arr = array();
        $collection = $this->getCollection();

        $arr[] = array('label' => 'None Selected', 'value' => '');

        foreach ($collection->getItems() as $group) {
            $arr[] = array('label' => $group->getEntityName(), 'value' => $group->getEntityId());
        }
        return $arr;
    }

    /**
     * Gets all of the customer ids linked with the group
     * 
     * @return array
     */
    public function getLinkedCustomers()
    {
        $collection = $this->commonResourceAccessGroupCustomerCollectionFactory->create();
        $collection->addFieldToFilter('group_id', $this->getId());
        return $collection->getItems();
    }

    /**
     * Gets all of the right ids linked with the group
     * 
     * @return array
     */
    public function getLinkedRights()
    {
        $collection = $this->commonResourceAccessGroupRightCollectionFactory->create();
        $collection->addFilter('group_id', $this->getId());
        return $collection->getItems();
    }

    /**
     * Checks whether a group has the right to access a given location
     * 
     * @param string $module
     * @param string $controller
     * @param string $action
     * @param string $block
     * @param string $actionType
     * @param integer $groupId
     * 
     * @return boolean
     */
    public function groupHasRight($module, $controller, $action, $block, $actionType, $groupId = null)
    {

        $hasRight = false;

        if (is_null($groupId)) {
            $groupId = $this->getId();
        }

        if (!empty($groupId)) {

            $rights = $this->getAllRights($groupId);

            if (!empty($rights)) {

                if (isset($rights[$module . '_' . $controller . '_' . $action . '_' . $block . '_' . $actionType]) ||
                    isset($rights[$module . '_' . $controller . '_*_' . $block . '_' . $actionType]) ||
                    isset($rights[$module . '_*_*_' . $block . '_' . $actionType])) {
                    $hasRight = true;
                }

                if ($module == 'Epicor_Supplierconnect' && $controller == 'Password' && $action == 'index') {
                    $hasRight = true;
                }
            }
        }

        return $hasRight;
    }

    /**
     * Gets an array of a groups rights
     * 
     * @param integer $groupId
     * 
     * @return array
     */
    public function getAllRights($groupId = null)
    {

        $rights = $this->getCachedRights($groupId);

        if (empty($rights)) {
            $collection = $this->commonResourceAccessGroupRightCollectionFactory->create();
            /* @var $collection Epicor_Common_Model_Resource_Access_Group_Right_Collection */
            $collection->addFilter('group_id', $groupId);
            $collection->getSelect()->joinLeft(array('re' => $collection->getTable('ecc_access_right_element')), 'main_table.right_id = re.right_id', array('re.right_id'), null);
            $collection->getSelect()->joinLeft(array('e' => $collection->getTable('ecc_access_element')), 're.element_id = e.id', array('e.*', 'e.id as entity_id'), null);
            $collection->getSelect()->group('e.id');

            $rights = array();
            foreach ($collection->getItems() as $right) {

                $key = $right->getModule() . '_' . $right->getController() . '_' . $right->getAction() . '_' . $right->getBlock() . '_' . $right->getActionType();

                $rights[$key] = 1;
            }

            $this->cacheRights($groupId, $rights);
        }

        return $rights;
    }

    /**
     * Attempts to load the groups rights data from cache
     * 
     * @param integer $groupId
     * 
     * @return array
     */
    public function getCachedRights($groupId = null)
    {
        $data = array();

        $registry = $this->registry->registry('getCachedRights');

        if (is_null($registry)) {
            $registry = array();
        }

        if (isset($registry[$groupId])) {
            $data = $registry[$groupId];
        } else {
            //M1 > M2 Translation Begin (Rule 12)
            //if (Mage::app()->useCache('access')) {
            if ($this->_cacheState->isEnabled(\Epicor\Common\Model\Cache\Type\Access::TYPE_IDENTIFIER)) {

                //$cache = Mage::app()->getCacheInstance();
                $cache = $this->_cacheManager;
                /* @var $cache Mage_Core_Model_Cache */
                //M1 > M2 Translation End
                $cacheKey = 'GROUP_' . $groupId . '_RIGHTS';

                $data = $cache->load($cacheKey);

                if (!empty($data)) {
                    $data = unserialize($data);
                }
            }
        }

        return $data;
    }

    /**
     * Caches the groups rights 
     * 
     * @param integer $groupId
     * @param array $data
     */
    private function cacheRights($groupId, $data)
    {
        $registry = $this->registry->registry('getCachedRights');

        if (is_null($registry)) {
            $registry = array();
        }

        //M1 > M2 Translation Begin (Rule 12)
        //if (Mage::app()->useCache('access')) {
        if ($this->_cacheState->isEnabled(\Epicor\Common\Model\Cache\Type\Access::TYPE_IDENTIFIER)) {
            //$cache = Mage::app()->getCacheInstance();
            $cache = $this->_cacheManager;
            //M1 > M2 Translation End
            /* @var $cache Mage_Core_Model_Cache */

            $cacheKey = 'GROUP_' . $groupId . '_RIGHTS';

            $lifeTime = $this->scopeConfig->getValue('epicor_common/accessrights/cache_lifetime', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $cache->save(serialize($data), $cacheKey, array('ACCESS'), $lifeTime);
        }

        $registry[$groupId] = $data;

        $this->registry->unregister('getCachedRights');
        $this->registry->register('getCachedRights', $registry, true);
    }

    /**
     * Clears the groups rights cache
     * 
     * @param integer $groupId - group id to clear (if blank then current id of object is used)
     */
    public function clearCache($groupId = null)
    {

        //M1 > M2 Translation Begin (Rule 12)
        //if (Mage::app()->useCache('access')) {
        if ($this->_cacheState->isEnabled(\Epicor\Common\Model\Cache\Type\Access::TYPE_IDENTIFIER)) {
            if (is_null($groupId)) {
                $groupId = $this->getId();
            }

            if (!empty($groupId)) {
                //$cache = Mage::app()->getCacheInstance();
                $cache = $this->_cacheManager;
                //M1 > M2 Translation End
                /* @var $cache Mage_Core_Model_Cache */
                $cacheKey = 'GROUP_' . $groupId . '_RIGHTS';
                $cache->remove($cacheKey);
            }
        }
    }

    /**
     * Deletes all of the groups customer and right links
     */
    public function _beforeDelete()
    {
        $this->clearCache();
        $this->deleteLinkedCustomers();
        $this->deleteLinkedRights();
        parent::_beforeDelete();
    }

    /**
     * Deletes all of the groups customer links
     */
    public function deleteLinkedCustomers()
    {
        $model = $this->commonAccessGroupCustomerFactory->create();
        $collection = $model->getCollection();
        $collection->addFilter('group_id', $this->getId());
        $items = $collection->getItems();
        //delete existing.
        foreach ($items as $cus) {
            $cus->delete();
        }
    }

    /**
     * Deletes all of the groups right links
     */
    public function deleteLinkedRights()
    {
        $model = $this->commonAccessGroupRightFactory->create();
        $collection = $model->getCollection();
        $collection->addFilter('group_id', $this->getId());
        $items = $collection->getItems();
        //delete existing.
        foreach ($items as $right) {
            $right->delete();
        }
    }

}
