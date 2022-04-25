<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ProcessStoreSave extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Flags any ERP accounts that need their brands refreshing due to a store group branding change
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        /* @var $store \Epicor\Comm\Model\Store */

        if (($store->isDeleted() || $store->isObjectNew()) && $this->scopeConfig->isSetFlag('Epicor_Comm/brands/erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $collection = $this->commResourceCustomerErpaccountCollectionFactory->create();
            /* @var $storeCollection \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\Store\Collection */
            $collection->addFieldToFilter('brands', array('neq' => ''));
            $collection->addFieldToFilter('brand_refresh', '0');

            foreach ($collection->getItems() as $account) {
                $account->setBrandRefresh(true);
                $account->save();
            }
        }
    }

}