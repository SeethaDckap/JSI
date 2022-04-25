<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ProcessWebsiteSaveBefore extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * trims any branding info
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $website = $observer->getEvent()->getWebsite();
        /* @var $website Mage_Core_Model_Website */

        $website->setEccCompany(trim($website->getEccCompany()));
        $website->setEccSite(trim($website->getEccSite()));
        $website->setEccWarehouse(trim($website->getEccWarehouse()));
        $website->setEccGroup(trim($website->getEccGroup()));

        $postData = $this->request->getPost();

        if (isset($postData['ecc_allowed_customer_types'])) {
            $website->setEccAllowedCustomerTypes(implode(',', $postData['ecc_allowed_customer_types']));
        } else {
            $website->setEccAllowedCustomerTypes('');
        }

        // save website lists 
        $selectedLists = array();
        $selectedLists = $website->getData('lists');
        $selectedLists = $selectedLists ? $selectedLists : array();
        $collection = $this->listsResourceListModelWebsiteCollectionFactory->create()->addFieldToFilter('website_id', array('eq' => $website->getWebsiteId()));  // get all relevant entries
        $existingLists = array();
        foreach ($collection as $coll) {
            $existingLists[] = $coll->getListId();                                                // save by listId  
        }

        $listsToAdd = array_diff($selectedLists, $existingLists);                                   // filter those to add
        $listsToDelete = array_diff($existingLists, $selectedLists);                                // filter those to delete

        foreach ($listsToAdd as $listToAdd) {
            $add = $this->listsListModelWebsiteFactory->create();
            $add->setListId($listToAdd);
            $add->setWebsiteId($website->getWebsiteId());
            $add->save();
        }
        foreach ($listsToDelete as $listToDelete) {
            $this->listsResourceListModelWebsiteCollectionFactory->create()->addFieldToFilter('website_id', $website->getWebsiteId())
                ->addFieldToFilter('list_id', $listToDelete)
                ->getFirstItem()
                ->delete();
        }
    }

}