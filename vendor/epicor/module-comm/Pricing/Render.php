<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Pricing;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

/**
 * Catalog Price Render
 *
 * @method string getPriceRender()
 * @method string getPriceTypeCode()
 */
class Render extends \Magento\Catalog\Pricing\Render
{
     /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * Construct
     *
     * @param Template\Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context,$registry, $data);
    }

    

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    protected function _toHtml()
    { 
     /*
     *  Magento Default Tier price should not come if location price is enabled & showing in product page.
     */
       $product = $this->getProduct(); 
       $location_count = count($product->getCustomerLocations());
       $location_status = $this->scopeConfig->getValue('epicor_comm_locations/global/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ; 
       $all_source_location =$this->scopeConfig->getValue('epicor_comm_locations/global/stockvisibility',
               \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'all_source_locations' ? true : false;
       
       if($product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE){
           return '';
        }
        
       if($all_source_location == false){ 
             if($location_status && $this->getDefaultTrierprice()){
                 if($location_count >1 ){
                     return '';
                 }
             }
       }
        
        /** @var PricingRender $priceRender */
        $priceRender = $this->getLayout()->getBlock($this->getPriceRender());
        if ($priceRender instanceof PricingRender) {
            //$product = $this->getProduct();
            if ($product instanceof SaleableInterface) {
                $arguments = $this->getData();
                $arguments['render_block'] = $this;
                return $priceRender->render($this->getPriceTypeCode(), $product, $arguments);
            }
        }
        return parent::_toHtml();
    }

    /**
     * Returns saleable item instance
     *
     * @return Product
     */
    protected function getProduct()
    { 
        $parentBlock = $this->getParentBlock();

        $product = $parentBlock && $parentBlock->getProductItem()
            ? $parentBlock->getProductItem()
            : $this->registry->registry('product');
        return $product;
    }
}
