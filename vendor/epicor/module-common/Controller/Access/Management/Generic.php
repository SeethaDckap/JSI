<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Access\Management;


/**
 * 
 * Customer Access Groups management controller
 * 
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 */
abstract class Generic extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Model\Access\Group\CustomerFactory
     */
    protected $commonAccessGroupCustomerFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Common\Model\Access\Group\RightFactory
     */
    protected $commonAccessGroupRightFactory;

    /**
     * @var \Epicor\Common\Model\Access\GroupFactory
     */
    protected $commonAccessGroupFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Common\Model\Access\Group\RightFactory $commonAccessGroupRightFactory,
        \Epicor\Common\Model\Access\GroupFactory $commonAccessGroupFactory,
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;
        $this->registry = $registry;
        $this->commonAccessGroupCustomerFactory = $commonAccessGroupCustomerFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->commonAccessGroupRightFactory = $commonAccessGroupRightFactory;
        $this->commonAccessGroupFactory = $commonAccessGroupFactory;
        $this->generic = $generic;
        parent::__construct(
            $context
        );
    }


    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        // a brute-force protection here would be nice

        parent::preDispatch();

        if (!$this->customerSession->authenticate($this) ||
            !$this->scopeConfig->isSetFlag('epicor_common/accessrights/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $this->setFlag('', 'no-dispatch', true);
            $this->_redirectUrl($this->_getRefererUrl());
        }
    }
/**
     * Loads the erp account for this customer
     * 
     * @return \Epicor\Comm\Model\Customer\Erpaccount
     */
    protected function loadErpAccount()
    {

        $customer = $this->customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */

        $helper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */

        if ($customer->isSupplier()) {
            $erpAccount = $helper->getErpAccountInfo(null, 'supplier');
        } else {
            $erpAccount = $helper->getErpAccountInfo();
        }

        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        $this->registry->register('access_erp_account', $erpAccount);

        return $erpAccount;
    }

    /**
     * Saves contacts to the group
     * 
     * @param array $data
     * @param \Epicor\Common\Model\Access\Group $group
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     */
    private function saveContacts($data, $group, $erpAccount)
    {

        $contacts = isset($data['contacts']) ? $data['contacts'] : array();
        $customer = $this->customerSession->getCustomer()->getId();

        $erpContacts = $this->_loadContacts($erpAccount);

        $model = $this->commonAccessGroupCustomerFactory->create();
        /* @var $model Epicor_Common_Model_Access_Group_Customer */

        $collection = $model->getCollection();
        /* @var $model Epicor_Common_Model_Access_Group_Customer */
        $collection->addFilter('group_id', $group->getId());
        $items = $collection->getItems();

        $existing = array();

        // Remove old - only if they're not passed in the data

        foreach ($items as $cus) {
            if (!in_array($cus->getCustomerId(), $contacts) && in_array($cus->getCustomerId(), $erpContacts) && $cus->getCustomerId() != $customer) {
                $cus->delete();
            } else {
                $existing[] = $cus->getCustomerId();
            }
        }

        // Add new - only if they don't already exist

        foreach ($contacts as $customerId) {
            if (!in_array($customerId, $existing) && in_array($customerId, $erpContacts) && $customerId != $customer) {
                $model = $this->commonAccessGroupCustomerFactory->create();
                /* @var $model Epicor_Common_Model_Access_Group_Customer */
                $model->setCustomerId($customerId);
                $model->setGroupId($group->getId());
                $model->save();
            }
        }
    }

    /**
     * Loads all the ids of contacts for the ERP Account
     * 
     * @param \Epicor\Comm\Model\Customer\Erpaccount $erpAccount
     * 
     * @return array
     */
    private function _loadContacts($erpAccount)
    {
        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        /* @var $collection Mage_Customer_Model_Resource_Customer_Collection */

        if ($erpAccount->isTypeSupplier()) {
            $collection->addAttributeToSelect('ecc_supplier_erpaccount_id');
            $collection->addAttributeToFilter('ecc_supplier_erpaccount_id', $erpAccount->getId());
        } else if ($erpAccount->isTypeCustomer()) {
            $collection->addAttributeToSelect('ecc_erpaccount_id');
            $collection->addAttributeToFilter('ecc_erpaccount_id', $erpAccount->getId());
        }

        $customerId = $this->customerSession->getCustomer()->getId();
        $collection->addFieldToFilter('entity_id', array('neq' => $customerId));

        return $collection->getAllIds();
    }

    /**
     * Saves rights to the group
     * 
     * @param array $data
     * @param \Epicor\Common\Model\Access\Group $group
     */
    private function saveRights($data, $group)
    {
        $rightIds = isset($data['rights']) ? $data['rights'] : array();

        $existing = array();

        $model = $this->commonAccessGroupRightFactory->create();
        /* @var $model Epicor_Common_Model_Access_Group_Right */

        $collection = $model->getCollection();
        /* @var $collection Epicor_Common_Model_Resource_Access_Group_Right_Collection */

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

    /**
     * Loads group by ID
     * 
     * @param integer $id
     * 
     * @return \Epicor\Common\Model\Access\Group
     */
    protected function _loadGroup($id)
    {
        $model = $this->commonAccessGroupFactory->create();
        /* @var $model Epicor_Common_Model_Access_Group */

        $erpAccount = $this->loadErpAccount();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */

        if ($id) {
            $model->load($id);
        }

        if (!$model->getId()) {
            $global = false;
            if (!empty($id)) {
                $this->generic->addError(__('Access Group does not exist'));
                $this->_redirect('*/*/');
            } else {
                $model = $this->commonAccessGroupFactory->create();
                /* @var $model Epicor_Common_Model_Access_Group */
            }
        } else {
            if ($model->getErpAccountId() && $model->getErpAccountId() != $erpAccount->getId()) {
                $this->generic->addError(__('Access Group does not exist'));
                $this->_redirect('*/*/');
            }

            $global = ($model->getErpAccountId()) ? false : true;
        }

        $this->registry->register('access_group', $model);
        $this->registry->register('access_group_global', $global);

        return $model;
    }

}
