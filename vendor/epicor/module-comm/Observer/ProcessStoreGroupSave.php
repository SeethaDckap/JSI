<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ProcessStoreGroupSave extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Flags any ERP accounts that need their brands refreshing due to a store group branding change
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeGroup = $observer->getEvent()->getStoreGroup();
        /* @var $storeGroup Mage_Core_Model_Store_Group */

        $origBrand = array(
            $storeGroup->getOrigData('ecc_company'),
            $storeGroup->getOrigData('ecc_site'),
            $storeGroup->getOrigData('ecc_warehouse'),
            $storeGroup->getOrigData('ecc_group')
        );

        $brand = array(
            $storeGroup->getData('ecc_company'),
            $storeGroup->getData('ecc_site'),
            $storeGroup->getData('ecc_warehouse'),
            $storeGroup->getData('ecc_group')
        );

        if (($storeGroup->isDeleted() || $origBrand != $brand) && $this->scopeConfig->isSetFlag('Epicor_Comm/brands/erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
            /* @var $storeCollection Epicor_Comm_Model_Resource_Customer_Erpaccount_Store_Collection */
            $collection->addFieldToFilter('brands', array('neq' => ''));
            $collection->addFieldToFilter('brand_refresh', '0');

            foreach ($collection->getItems() as $account) {
                $account->setBrandRefresh(true);
                $account->save();
            }
        }
        $existingLists = array();
        //save store list
        $selectedLists = is_array($storeGroup->getData('lists')) ? $storeGroup->getData('lists') : array();
        $collection = $this->listsResourceListModelStoreGroupCollectionFactory->create()->addFieldToFilter('store_group_id', array('eq' => $storeGroup->getGroupId()));
        foreach ($collection as $coll) {
            $existingLists[] = $coll->getListId();
        }

        $listsToAdd = array_diff($selectedLists, $existingLists);
        $listsToDelete = array_diff($existingLists, $selectedLists);

        foreach ($listsToAdd as $listToAdd) {
            $add = $this->listsListModelStoreGroupFactory->create();
            $add->setListId($listToAdd);
            $add->setStoreGroupId($storeGroup->getGroupId());
            $add->save();
        }
        foreach ($listsToDelete as $listToDelete) {
            $this->listsResourceListModelStoreGroupCollectionFactory->create()->addFieldToFilter('store_group_id', $storeGroup->getGroupId())
                ->addFieldToFilter('list_id', $listToDelete)
                ->getFirstItem()
                ->delete();
        }
    }

}