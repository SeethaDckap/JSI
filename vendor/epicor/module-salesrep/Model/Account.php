<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model;


/**
 * Model Class for Sales Rep Account
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 * 
 * @method string getSalesRepId()
 * @method string getName()
 * 
 * @method setSalesRepId(string $value)
 * @method setName(string $value)
 * 
 */
class Account extends \Epicor\Common\Model\AbstractModel
{

    protected $_erpAccounts;
    protected $_erpAccountIds;
    protected $_newErpAccounts = array();
    protected $_deleteErpAccounts;
    protected $_priceLists;
    protected $_pricingRules;
    protected $_salesReps;
    protected $_childAccounts = array();
    protected $_childAccountsIds = array();
    protected $_hierarchyChildAccounts = array();
    protected $_hierarchyChildAccountsIds = array();
    protected $_newChildAccounts = array();
    protected $_deleteChildAccounts = array();
    protected $_parentAccounts = array();
    protected $_parentAccountsIds = array();
    protected $_hierarchyParentAccounts = array();
    protected $_hierarchyParentAccountsIds = array();
    protected $_newParentAccounts = array();
    protected $_deleteParentAccounts = array();
    protected $_company;
    protected $_suppliedErpAccounts;

    /**
     * @var \Epicor\SalesRep\Model\ErpaccountFactory
     */
    protected $salesRepErpaccountFactory;

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Erpaccount\CollectionFactory
     */
    protected $salesRepResourceErpaccountCollectionFactory;

    /**
     * @var \Epicor\SalesRep\Model\HierarchyFactory
     */
    protected $salesRepHierarchyFactory;

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Hierarchy\CollectionFactory
     */
    protected $salesRepResourceHierarchyCollectionFactory;

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\CollectionFactory
     */
    protected $salesRepResourcePricingRuleCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory
     */
    protected $salesRepResourceAccountCollectionFactory;

    protected $_masqueradeAccounts;
    protected $_masqueradeAccountIds;
    
    /**
     * @var \Epicor\Common\Helper\XmlFactory
     */
    protected $commonHelper;    

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Data $commonHelper,
        \Epicor\SalesRep\Model\ErpaccountFactory $salesRepErpaccountFactory,
        \Epicor\SalesRep\Model\ResourceModel\Erpaccount\CollectionFactory $salesRepResourceErpaccountCollectionFactory,
        \Epicor\SalesRep\Model\HierarchyFactory $salesRepHierarchyFactory,
        \Epicor\SalesRep\Model\ResourceModel\Hierarchy\CollectionFactory $salesRepResourceHierarchyCollectionFactory,
        \Epicor\SalesRep\Model\ResourceModel\Pricing\Rule\CollectionFactory $salesRepResourcePricingRuleCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Epicor\SalesRep\Model\ResourceModel\Account\CollectionFactory $salesRepResourceAccountCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->salesRepErpaccountFactory = $salesRepErpaccountFactory;
        $this->salesRepResourceErpaccountCollectionFactory = $salesRepResourceErpaccountCollectionFactory;
        $this->salesRepHierarchyFactory = $salesRepHierarchyFactory;
        $this->salesRepResourceHierarchyCollectionFactory = $salesRepResourceHierarchyCollectionFactory;
        $this->salesRepResourcePricingRuleCollectionFactory = $salesRepResourcePricingRuleCollectionFactory;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->salesRepResourceAccountCollectionFactory = $salesRepResourceAccountCollectionFactory;
        $this->commonHelper = $commonHelper;
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
        $this->_init('Epicor\SalesRep\Model\ResourceModel\Account');
    }

    public function afterSave()
    {
        $erpAccounts = array_filter($this->_newErpAccounts);    // remove empty elements
        if (!empty($erpAccounts)) {
            $existingIds = $this->getErpAccountIds();
            foreach ($erpAccounts as $erpAccountId) {

                if (!in_array($erpAccountId, $existingIds)) {
                    $erpAccount = $this->salesRepErpaccountFactory->create();
                    /* @var $erpAccount Epicor_SalesRep_Model_Erpaccount */
                    $erpAccount->setErpAccountId($erpAccountId);
                    $erpAccount->setSalesRepAccountId($this->getId());

                    $erpAccount->save();
                }
            }
        }

        if (!empty($this->_deleteErpAccounts)) {

            $existingIds = $this->getErpAccountIds();
            foreach ($this->_deleteErpAccounts as $erpAccountId) {
                if (in_array($erpAccountId, $existingIds)) {
                    $collection = $this->salesRepResourceErpaccountCollectionFactory->create();
                    $collection->addFieldToFilter('sales_rep_account_id', $this->getId());
                    $collection->addFieldToFilter('erp_account_id', $erpAccountId);
                    $erpAccount = $collection->getFirstItem();
                    /* @var $erpAccount Epicor_SalesRep_Model_Erpaccount */
                    $erpAccount->delete();
                }
            }
        }

        if (!empty($this->_deleteChildAccounts)) {
            $this->deleteChildAccounts($this->_deleteChildAccounts);
        }

        if (!empty($this->_deleteParentAccounts)) {
            $this->deleteParentAccounts($this->_deleteParentAccounts);
        }

        if (!empty($this->_newChildAccounts)) {
            $this->addChildAccounts($this->_newChildAccounts);
        }

        if (!empty($this->_newParentAccounts)) {
            $this->addParentAccounts($this->_newParentAccounts);
        }

        parent::afterSave();
    }




    public function afterDelete()
    {
        $this->deleteChildAccounts();
        $this->deleteParentAccounts();
        $this->deleteErpAccounts();
        $this->deletePriceLists();
        $this->removeFromCustomers();

        parent::afterDelete();
    }

    protected function addChildAccounts($ids = array())
    {

        $existingIds = $this->getChildAccountsIds();

        foreach ($ids as $id) {
            if (!in_array($id, $existingIds)) {
                $erpAccount = $this->salesRepHierarchyFactory->create();
                /* @var $erpAccount \Epicor\SalesRep\Model\Hierarchy */
                $erpAccount->setParentSalesRepAccountId($this->getId());
                $erpAccount->setChildSalesRepAccountId($id);
                $erpAccount->save();
            }
        }
    }

    protected function addParentAccounts($ids = array())
    {

        $existingIds = $this->getParentAccountsIds();

        foreach ($ids as $id) {
            if (!in_array($id, $existingIds)) {
                $erpAccount = $this->salesRepHierarchyFactory->create();
                /* @var $erpAccount \Epicor\SalesRep\Model\Hierarchy */
                $erpAccount->setChildSalesRepAccountId($this->getId());
                $erpAccount->setParentSalesRepAccountId($id);
                $erpAccount->save();
            }
        }
    }

    protected function deleteChildAccounts($ids = array())
    {
        $collection = $this->salesRepResourceHierarchyCollectionFactory->create();
        $collection->addFieldToFilter('parent_sales_rep_account_id', $this->getId());

        if (!empty($ids)) {
            $collection->addFieldToFilter('child_sales_rep_account_id', array('in' => $ids));
        }

        foreach ($collection as $item) {
            $item->delete();
        }
    }

    protected function deleteParentAccounts($ids = array())
    {
        $collection = $this->salesRepResourceHierarchyCollectionFactory->create();
        $collection->addFieldToFilter('child_sales_rep_account_id', $this->getId());

        if (!empty($ids)) {
            $collection->addFieldToFilter('parent_sales_rep_account_id', array('in' => $ids));
        }

        foreach ($collection as $item) {
            $item->delete();
        }
    }

    protected function deleteErpAccounts()
    {
        $collection = $this->salesRepResourceErpaccountCollectionFactory->create();
        $collection->addFieldToFilter('sales_rep_account_id', $this->getId());

        foreach ($collection as $item) {
            $item->delete();
        }
    }

    protected function deletePriceLists()
    {
        $collection = $this->salesRepResourcePricingRuleCollectionFactory->create();
        $collection->addFieldToFilter('sales_rep_account_id', $this->getId());

        foreach ($collection as $item) {
            $item->delete();
        }
    }

    protected function removeFromCustomers()
    {
        $collection = $this->customerResourceModelCustomerCollectionFactory->create();
        $collection->addAttributeToSelect('ecc_sales_rep_account_id');
        $collection->addAttributeToFilter('ecc_sales_rep_account_id', $this->getId());

        foreach ($collection as $item) {
            $item->setEccSalesRepAccountId(false);
            $item->save();
        }
    }

    /**
     * 
     * @return array
     */
    public function getMasqueradeAccounts($ids = false)
    {
        if (is_null($this->_masqueradeAccounts)) {
            $this->_masqueradeAccounts = $this->getErpAccounts();
            $this->_masqueradeAccountIds = $this->getErpAccounts(true);

            foreach ($this->getChildAccounts() as $childAccount) {
                /* @var $childAccount \Epicor\SalesRep\Model\Account */
                $this->_masqueradeAccounts = $this->_masqueradeAccounts + $childAccount->getMasqueradeAccounts();
                $this->_masqueradeAccountIds = array_merge($this->_masqueradeAccountIds, $childAccount->getMasqueradeAccounts(true));
            }
        }

        return ($ids) ? $this->_masqueradeAccountIds : $this->_masqueradeAccounts;
    }

    public function getMasqueradeAccountsWithoutChild($ids = false)
    {
        if (is_null($this->_masqueradeAccounts)) {
            $this->_masqueradeAccounts = $this->getErpAccounts();
        }

        return ($ids) ? $this->_masqueradeAccountIds : $this->_masqueradeAccounts;
    }

    public function getMasqueradeAccountIds()
    {
        return $this->getMasqueradeAccounts(true);
    }

    /**
     * 
     * @param \Epicor\Comm\Model\Customer $customer
     * @param bool $validateForStore
     * @param \Epicor\Comm\Model\Store $store
     * @return array
     */
    public function getStoreMasqueradeAccounts($store = null, $ids = false)
    {

        /* @var $customer \Epicor\Comm\Model\Customer */
        $erpAccounts = array();

        /* @var $salesRepAccount \Epicor\SalesRep\Model\Account */
        foreach ($this->getMasqueradeAccounts() as $id => $erpAccount) {
            /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
            if ($erpAccount->checkBranding($store)) {
                $erpAccounts[$erpAccount->getId()] = $erpAccount;
            }
        }

        return ($ids) ? array_keys($erpAccounts) : $erpAccounts;
    }

    /**
     * 
     * @param \Epicor\Comm\Model\Customer $customer
     * @param bool $validateForStore
     * @param \Epicor\Comm\Model\Store $store
     * @return array
     */
    public function getStoreMasqueradeAccountsNoChild($store = null, $ids = false)
    {
        /* @var $customer \Epicor\Comm\Model\Customer */
        $erpAccounts = array();

        /* @var $salesRepAccount \Epicor\SalesRep\Model\Account */
        foreach ($this->getMasqueradeAccountsWithoutChild() as $id => $erpAccount) {
            /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
            if ($erpAccount->checkBranding($store)) {
                $erpAccounts[$erpAccount->getId()] = $erpAccount;
            }
        }
        return ($ids) ? array_keys($erpAccounts) : $erpAccounts;
    }

    /**
     * 
     * @param \Epicor\Comm\Model\Customer $customer
     * @param bool $validateForStore
     * @param \Epicor\Comm\Model\Store $store
     * @return array
     */
    public function getStoreMasqueradeAccountIds($store = null)
    {
        return $this->getStoreMasqueradeAccounts($store, true);
    }

    public function getErpAccountIds()
    {
        return $this->getErpAccounts(true);
    }

    public function addErpAccount($id)
    {
        $this->_newErpAccounts[] = $id;
        $this->_hasDataChanges = true;
    }

    public function removeErpAccount($id)
    {
        $this->_deleteErpAccounts[] = $id;
        $this->_hasDataChanges = true;
    }

    public function getParentAccountsIds()
    {
        return $this->getParentAccounts(true);
    }

    public function getHierarchyParentAccountsIds()
    {
        return $this->getHierarchyParentAccounts(true);
    }

    public function addParentAccount($id)
    {
        $hierarchyChildAccounts = $this->getHierarchyChildAccounts(true);
        $children = $hierarchyChildAccounts + $this->_newChildAccounts;
        if (in_array($id, $children)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A Parent Sales Rep Account is already in the Hierarchy Line as a Child, please remove it from the Hierarchy Line before adding it.'));
        } else {
            $this->_newParentAccounts[] = $id;
            $this->_hasDataChanges = true;
        }
    }

    public function removeParentAccount($id)
    {
        $this->_deleteParentAccounts[] = $id;
        $this->_hasDataChanges = true;
    }

    public function getChildAccountsIds()
    {
        return $this->getChildAccounts(true);
    }

    public function getHierarchyChildAccountsIds()
    {
        return $this->getHierarchyChildAccounts(true);
    }

    public function addChildAccount($id)
    {
        $hierarchyParentAccounts = $this->getHierarchyParentAccounts(true);
        $parents = $hierarchyParentAccounts + $this->_newParentAccounts;
        if (in_array($id, $parents)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('A Child Sales Rep Account is already in the Hierarchy Line as a Parent, please remove it from the Hierarchy Line before adding it.'));
        } else {
            $this->_newChildAccounts[] = $id;
            $this->_hasDataChanges = true;
        }
    }

    public function removeChildAccount($id)
    {
        $this->_deleteChildAccounts[] = $id;
        $this->_hasDataChanges = true;
    }

    /**
     * Get Sales Rep Price Lists
     * TODO create sales price lists
     * 
     * @return array
     */
    public function getPriceLists()
    {
        if (is_null($this->_priceLists)) {
            $this->_priceLists = array();
        }

        return $this->_priceLists;
    }

    /**
     * Gets all Pricing Rules associated with this Sales Rep Account
     * 
     * @return array
     */
    public function getPricingRules()
    {
        if (is_null($this->_pricingRules)) {
            $this->_pricingRules = array();
            $this->_pricingRules = $this->salesRepResourcePricingRuleCollectionFactory->create()
                ->addFieldToFilter('sales_rep_account_id', $this->getId())
                ->getItems();
        }

        return $this->_pricingRules;
    }

    /**
     * Gets all sales reps collection
     * 
     * @return array
     */
    public function getSalesRepsCollection($website = null)
    {
        return $this->getSalesReps($website, true);
    }

    /**
     * Gets all sales reps associated with this Sales Rep Account
     * 
     * @return array
     */
    public function getSalesReps($website = null, $returnCollection = false)
    {
        if (is_null($this->_salesReps) || $returnCollection) {
            $this->_salesReps = array();
            $collection = $this->customerResourceModelCustomerCollectionFactory->create()
                ->addNameToSelect()
                ->addAttributeToFilter('ecc_sales_rep_account_id', $this->getId());

            if (!empty($website)) {
                $collection->addFieldToFilter('website_id', $website);
            }

            if ($returnCollection) {
                return $collection;
            }

            $this->_salesReps = $collection->getItems();
        }

        return $this->_salesReps;
    }

    /**
     * Gets the ERP account linkages
     * 
     * @param boolean $ids - whether to return just ids or whole objects
     * 
     * @return array
     */
    public function getErpAccounts($ids = false)
    {
        if (is_null($this->_erpAccounts)) {

            $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
            /* @var $collection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Collection */
            $collection->join(array('erp' => 'ecc_salesrep_erp_account'), 'main_table.entity_id = erp.erp_account_id', '');
            $collection->addFieldToFilter('erp.sales_rep_account_id', $this->getId());
            $helper = $this->commonHelper;
            $dealerLicense = $helper->checkDealerLicense();     
            if(!$dealerLicense) {
              $collection->getSelect()->where("main_table.account_type <> 'Dealer' AND main_table.account_type <> 'Distributor' OR main_table.account_type is null");  
            }            
            $this->_erpAccounts = array();
            #$this->_erpAccountIds = $collection->getAllIds(); <- Why not this ?
            $this->_erpAccountIds = array();
            foreach ($collection->getItems() as $erpAccountLink) {
                $this->_erpAccountIds[] = $erpAccountLink->getEntityId();
                $this->_erpAccounts[$erpAccountLink->getEntityId()] = $erpAccountLink;
            }
        }

        return ($ids) ? $this->_erpAccountIds : $this->_erpAccounts;
    }

    /**
     * Gets the Children Hierarchy linkages
     * 
     * @param boolean $ids - whether to return just ids or whole objects
     * 
     * @return array
     */
    public function getChildAccounts($ids = false)
    {
        if (empty($this->_childAccounts)) {

            $collection = $this->salesRepResourceAccountCollectionFactory->create();
            /* @var $collection \Epicor\SalesRep\Model\ResourceModel\Account\Collection */
            $collection->join(array('linkage' => 'ecc_salesrep_hierarchy'), 'main_table.id = linkage.child_sales_rep_account_id', 'parent_sales_rep_account_id');
            $collection->addFieldToFilter('parent_sales_rep_account_id', array('eq' => $this->getId()));

            $this->_childAccounts = $collection->getItems();

            $this->_childAccountsIds = array();
            foreach ($collection->getItems() as $erpAccountLink) {
                /* @var $erpAccountLink \Epicor\SalesRep\Model\Account */
                $this->_childAccountsIds[] = $erpAccountLink->getId();
            }
        }

        return ($ids) ? $this->_childAccountsIds : $this->_childAccounts;
    }

    /**
     * Gets the All the Children Hierarchy linkages under this Account
     * 
     * @param boolean $ids - whether to return just ids or whole objects
     * 
     * @return array
     */
    public function getHierarchyChildAccounts($ids = false)
    {
        if (empty($this->_hierarchyChildAccounts)) {

            $children = $this->getChildAccounts();
            $hierarchyChildren = $children;

            foreach ($children as $child) {
                $hierarchyChildren = $hierarchyChildren + $child->getHierarchyChildAccounts();
            }

            $this->_hierarchyChildAccounts = $hierarchyChildren;

            $this->_hierarchyChildAccountsIds = array();
            foreach ($hierarchyChildren as $hierarchyChild) {
                $this->_hierarchyChildAccountsIds[] = $hierarchyChild->getId();
            }
        }

        return ($ids) ? $this->_hierarchyChildAccountsIds : $this->_hierarchyChildAccounts;
    }

    public function hasChildAccount($id)
    {
        return in_array($id, $this->getHierarchyChildAccountsIds());
    }

    /**
     * Gets the Children Hierarchy linkages
     * 
     * @param boolean $ids - whether to return just ids or whole objects
     * 
     * @return array
     */
    public function getParentAccounts($ids = false)
    {
        if (empty($this->_parentAccounts)) {
            $collection = $this->salesRepResourceAccountCollectionFactory->create();
            /* @var $collection \Epicor\SalesRep\Model\ResourceModel\Account\Collection */
            $collection->join(array('linkage' => 'ecc_salesrep_hierarchy'), 'main_table.id = linkage.parent_sales_rep_account_id', 'child_sales_rep_account_id');
            $collection->addFieldToFilter('child_sales_rep_account_id', array('eq' => $this->getId()));
            $this->_parentAccounts = $collection->getItems();
            $this->_parentAccountsIds = array();
            foreach ($collection->getItems() as $erpAccountLink) {
                /* @var $erpAccountLink \Epicor\SalesRep\Model\Account */
                $this->_parentAccountsIds[] = $erpAccountLink->getId();
            }
        }

        return ($ids) ? $this->_parentAccountsIds : $this->_parentAccounts;
    }

    /**
     * Gets the All the Parents Hierarchy linkages under this Account
     * 
     * @param boolean $ids - whether to return just ids or whole objects
     * 
     * @return array
     */
    public function getHierarchyParentAccounts($ids = false)
    {
        if (empty($this->_hierarchyParentAccounts)) {

            $parents = $this->getParentAccounts();
            $hierarchyParents = $parents;

            foreach ($parents as $parent) {
                $hierarchyParents = $hierarchyParents + $parent->getHierarchyParentAccounts();
            }

            $this->_hierarchyParentAccounts = $hierarchyParents;

            $this->_hierarchyParentAccountsIds = array();
            foreach ($hierarchyParents as $hierarchyParent) {
                $this->_hierarchyParentAccountsIds[] = $hierarchyParent->getId();
            }
        }

        return ($ids) ? $this->_hierarchyParentAccountsIds : $this->_hierarchyParentAccounts;
    }

    /**
     * Sets the sales rep account erp account linkages based on the array of erp account id's provided
     * 
     * @param array $erpAccounts
     */
    public function setErpAccounts($erpAccounts)
    {
        $newExisting = array();
        $existing = $this->getErpAccountIds();

        foreach ($existing as $erpAccountId) {
            if (!in_array($erpAccountId, $erpAccounts)) {
                $this->removeErpAccount($erpAccountId);
            } else {
                $newExisting[] = $erpAccountId;
            }
        }
        foreach ($erpAccounts as $erpAccountId) {
            if (!in_array($erpAccountId, $existing)) {
                $this->addErpAccount($erpAccountId);
            }
        }
    }

    /**
     * Sets the sales rep account children hierarchy linkages based on the array of sales rep id's provided
     * 
     * @param array $children
     */
    public function setChildAccounts($children)
    {
        $newExisting = array();
        $existing = $this->getChildAccountsIds();
        foreach ($existing as $childId) {
            if (!in_array($childId, $children)) {
                $this->removeChildAccount($childId);
            } else {
                $newExisting[] = $childId;
            }
        }

        foreach ($children as $childId) {
            if (!in_array($childId, $existing)) {
                $this->addChildAccount($childId);
            }
        }
    }

    /**
     * Sets the sales rep account parents hierarchy linkages based on the array of sales rep id's provided
     * 
     * @param array $parents
     */
    public function setParentAccounts($parents)
    {
        $newExisting = array();
        $existing = $this->getParentAccountsIds();

        foreach ($existing as $parentId) {
            if (!in_array($parentId, $parents)) {
                $this->removeParentAccount($parentId);
            } else {
                $newExisting[] = $parentId;
            }
        }

        foreach ($parents as $parentId) {
            if (!in_array($parentId, $existing)) {
                $this->addParentAccount($parentId);
            }
        }
    }

    public function getEncodedId()
    {
        return base64_encode(serialize($this->getId()));
    }

    public function canMasqueradeAs($erpAccountId)
    {
        $allowedIds = $this->getMasqueradeAccountIds();

        return in_array($erpAccountId, $allowedIds);
    }

    public function isManager()
    {
        $children = $this->getChildAccountsIds();

        return !empty($children);
    }

    public function setCompanies($companies)
    {
        $existingCompanies = unserialize($this->getCompany());
        $existingCompanies = is_array($existingCompanies) ? $existingCompanies : array();
        foreach ($companies as $company) {
            if (!in_array($company, $existingCompanies)) {
                $existingCompanies[] = $company;
            }
        }
        $this->setCompany(serialize($existingCompanies));
    }

}
