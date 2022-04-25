<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Observer;

class UpdateSiteCode extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Remove Default Fields to Website / Store Group Edit Pages
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * 
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $website = $observer->getEvent()->getWebsite();
        $store = $observer->getEvent()->getStore();

        if ($website) {
            $is_website = 1;
            $child_id = $website->getId();
            $code = $website->getCode();
        } else {
            $is_website = 0;
            $child_id = $store->getId();
            $code = $store->getCode();
        }

        $site = $this->hostingManagerSiteFactory->create();
        /* @var $site \Epicor\HostingManager\Model\Site */

        $site->loadByAttributes(array('is_website' => $is_website, 'child_id' => $child_id));

        if ($site->getId() && $site->getCode() != $code) {
            $site->setCode($code)->save();
        }
    }

}