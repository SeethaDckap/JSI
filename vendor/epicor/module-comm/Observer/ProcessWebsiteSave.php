<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ProcessWebsiteSave extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Flags any ERP accounts that need their brands refreshing due to a website branding change
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $website = $observer->getEvent()->getWebsite();
        /* @var $website Mage_Core_Model_Website */

        $origBrand = array(
            $website->getOrigData('ecc_company'),
            $website->getOrigData('ecc_site'),
            $website->getOrigData('ecc_warehouse'),
            $website->getOrigData('ecc_group')
        );

        $brand = array(
            $website->getData('ecc_company'),
            $website->getData('ecc_site'),
            $website->getData('ecc_warehouse'),
            $website->getData('ecc_group')
        );

        if (($website->isDeleted() || $origBrand != $brand) && $this->scopeConfig->isSetFlag('Epicor_Comm/brands/erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
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