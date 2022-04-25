<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Html;

class Breadcrumbs 
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;
    
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ){
        $this->request = $request;
        $this->productMetadata = $productMetadata;
    }
    
    /**
     * Fix for Magento 2.2.4 core issue of Product Image does not load if Product Name contains a double quotation mark
     * Needs to be removed once its fixed by Magento
     * 
     * @param \Magento\Theme\Block\Html\Breadcrumbs $subject
     * @param string $template
     * @return string
     */
    public function aftergetTemplate(\Magento\Theme\Block\Html\Breadcrumbs $subject, $template) 
    {
        $moduleName       = $this->request->getModuleName();
        $controllerName   = $this->request->getControllerName();
        $actionName       = $this->request->getActionName();
    
        
        if ((($moduleName == 'catalog' && $controllerName == 'product' && $actionName == 'view') || 
            (($moduleName == 'checkout' && $controllerName == 'cart' && $actionName == 'configure')) ||
            (($moduleName == 'wishlist' && $controllerName == 'index' && $actionName == 'configure'))
           ) && $this->productMetadata->getVersion() >= '2.2.0') {
            return 'Epicor_Comm::product/breadcrumbs.phtml';
        } 
        
        
        
        return $template;
    }
}
