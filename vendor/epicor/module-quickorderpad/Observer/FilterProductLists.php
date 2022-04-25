<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\QuickOrderPad\Observer;

class FilterProductLists extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Quickorderpad
     */
    protected $listsQopHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    protected $request;

    public function __construct(
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Epicor\Lists\Helper\Frontend\Quickorderpad $listsQopHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->escaper = $escaper;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->listsQopHelper = $listsQopHelper;
        $this->registry = $registry;
        $this->request = $request;
        
        parent::__construct($checkoutCart, $checkoutSession, $storeManager);
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        
        if($this->request->getModuleName() =='quickorderpad'){

            $collection = $observer->getEvent()->getCollection();
            $locHelper = $this->commLocationsHelper;
            /* @var $locHelper Epicor_Comm_Helper_Locations */
            $list = $this->listsQopHelper->getSessionList();
            
                if ($this->listsQopHelper->listsEnabled() && $list) {
                    $listProducts = $this->listsQopHelper->getProductIdsByList($list, true);
                    if (!$this->registry->registry('QOP_list_product_filter_applied') && !empty($listProducts)) {
                        $collection->getSelect()->where('(e.entity_id IN(' . implode(',', $listProducts) . '))');
                        $this->registry->register('QOP_list_product_filter_applied', true);
                    }
                }
        }
        
        return $this;
    }

}
