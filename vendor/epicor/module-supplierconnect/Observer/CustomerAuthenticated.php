<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Supplierconnect\Observer;

class CustomerAuthenticated extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Entityreg $commEntityregHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Epicor\Supplierconnect\Model\ModelReader $modelReader,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\CacheInterface $cache
    )
    {
        $this->cache = $cache;
        parent::__construct($registry, $commEntityregHelper, $eventManager, $modelReader,$customerSession);
    }

    /**
     * Clears the cache for any supplierconnect searches
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return  $this;
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getModel();
        $tags = array('CUSTOMER_' . $customer->getId() . '_SUPPLIERCONNECT_SEARCH');
        $cache = $this->cache;
        $cache->clean($tags);
        return $this;
    }

}