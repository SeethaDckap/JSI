<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Observer;

class CustomerAuthenticated extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    public function __construct(
        \Magento\Framework\App\CacheInterface $cache
    )
    {
        $this->cache = $cache;
    }


    /**
     * Clears the cache for any customerconnect searches
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getModel();
        $tags = array('CUSTOMER_' . $customer->getId() . '_CUSTOMERCONNECT_SEARCH');

        //M1 > M2 Translation Begin (Rule p2-6.7)
        //$cache = Mage::app()->getCacheInstance();
        $cache = $this->cache;
        //M1 > M2 Translation End
        /* @var $cache Mage_Core_Model_Cache */
        $cache->clean($tags);

        return $this;
    }

}