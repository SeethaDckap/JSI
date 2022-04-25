<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Product;

class Lazyload extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    
    /**     
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     *
     * @var \Epicor\Comm\Helper\Product 
     */
    protected $_commProductHelper;
    
    /**
     * 
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Epicor\Comm\Helper\Product $commProductHelper
     */
    public function __construct(
        \Magento\Framework\Registry $registry, 
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\Product $commProductHelper
    ) {
        $this->_registry = $registry;
        $this->_request  = $request;
        $this->_scopeConfig = $scopeConfig;
        $this->_commProductHelper = $commProductHelper;
    }
        
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $handle = $this->_request->getFullActionName();
        $isAjax = $this->_request->isAjax();
        $isLazyload = $this->_commProductHelper->isLazyLoad("view");
        if ($handle == "catalog_product_view" && !$isAjax && $isLazyload) {             
            $layout = $observer->getLayout();
            /* @var $layout \Epicor\Common\Model\Layout */     
            
            $elementtLists = $this->_commProductHelper->getElemetRemovelist();
            foreach ($elementtLists as $view) {
                if($layout->hasElement($view)){
                    $layout->unsetElement($view);
                }
            }                         
            return $this;
        }
    }

}