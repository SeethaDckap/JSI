<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\SalesRep\Block\Crqs\Details\Lines\Renderer;


/**
 * CRQ line currency column renderer
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Currency extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer\Currency
{

    /**
     * @var \Epicor\SalesRep\Helper\Data
     */
    protected $salesRepHelper;

    /**
     * @var \Epicor\SalesRep\Helper\Pricing\Rule\Product
     */
    protected $salesRepPricingRuleProductHelper;
    
    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Epicor\SalesRep\Helper\Pricing\Rule\Product $salesRepPricingRuleProductHelper,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        array $data = []
    )
    {
        $this->salesRepHelper = $salesRepHelper;
        $this->salesRepPricingRuleProductHelper = $salesRepPricingRuleProductHelper;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        parent::__construct(
            $context,
            $registry,
            $commMessagingHelper,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $_showDiscountField = true;
        $salesRepHelper = $this->salesRepHelper;
        $pricingRuleProductHelper = $this->salesRepPricingRuleProductHelper;
        $rowProduct = $this->customerconnectMessagingHelper->getProductObject((string) $row->getData('product_code'));
        
        if($this->getRequest()->getActionName() == "duplicate"){
           $rowProduct = $row->getProduct();
        }
        
        if ($rowProduct->getTypeId() == 'grouped' && $rowProduct->getEccDefaultUom() !=  $row->getData('unit_of_measure_code')) {
            $uomSku = (string) $row->getData('product_code') . $this->salesRepHelper->getUOMSeparator() . $row->getData('unit_of_measure_code');
            $uomProduct = $this->customerconnectMessagingHelper->getProductObject((string) $uomSku);
            $rowProduct = (empty($uomProduct->getData())) ? $rowProduct : $uomProduct;
         }
         
        /* @var $rowProduct \Epicor\Comm\Model\Product */

        if ($salesRepHelper->isEnabled() && $this->registry->registry('rfqs_editable') && ($rowProduct instanceof \Epicor\Comm\Model\Product && !$rowProduct->isObjectNew()) && $pricingRuleProductHelper->hasActiveRules()) {
            $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
            $rfq = $this->registry->registry('customer_connect_rfq_details');
            $index = $this->getColumn()->getIndex();

            $helper = $this->commMessagingHelper;
            /* @var $helper \Epicor\Comm\Helper\Messaging */

            $currency = $helper->getCurrencyMapping($rfq->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
            $currencySymbol = $helper->getCurrencySymbol($currency);
            $price = number_format($row->getData($index), 2, '.', '');
            $uniqueId = $row->getUniqueId();
            
            $use_msq_values = false;
            if ($rowProduct->getEccConfigurator()) {
                    $regisrty_key = 'rfq_discount_product_'.$rowProduct->getSku().$this->getGroupSequence($row);
                    $configurator_pricevalues = $this->registry->registry($regisrty_key);
                    if(isset($configurator_pricevalues) && is_array($configurator_pricevalues) && count($configurator_pricevalues)>0){
                       $use_msq_values = true;
                    }
             }
            if($use_msq_values && $configurator_pricevalues['basePrice']!= null 
                    && $configurator_pricevalues['rulePrice']!=null 
                    && $configurator_pricevalues['minPrice']!=null 
                    && $configurator_pricevalues['maxDiscount']!=null){
                
                    $basePrice = $configurator_pricevalues['basePrice']; 
                    $rulePrice = $configurator_pricevalues['rulePrice'];
                    $minPrice = $configurator_pricevalues['minPrice'];
                    $maxDiscount = $configurator_pricevalues['maxDiscount'];
                    
            }else{
                $basePrice = $pricingRuleProductHelper->getBasePrice($rowProduct, $row->getData('quantity')); 
                $rulePrice = $pricingRuleProductHelper->getRuleBasePrice($rowProduct, $basePrice, $row->getData('quantity'));
                $minPrice = $pricingRuleProductHelper->getMinPrice($rowProduct, $basePrice);
                $maxDiscount = $pricingRuleProductHelper->getMaxDiscount($rowProduct, $basePrice);
            }
            
            $discountPercent = $pricingRuleProductHelper->getDiscountAmount($price, $rulePrice);
            
            if ($row->getIsKit() == 'C') {
                $_showDiscountField = false;
            }
            
            if ($basePrice > 0 && $rulePrice > 0 && $rulePrice > $minPrice && $_showDiscountField) {

                $resetStyle = ($basePrice == $price) ? 'style="display:none"' : '';
                $resetLink = '<div id="reset_discount_' . $uniqueId . '" ' . $resetStyle . ' ><a href="javascript:salesrepPricing.resetDiscount(\'' . $uniqueId . '\')">' . __('Revert to Web Price') . '</a></div>';

                $html = '
                    <div class="salesrep-discount-container" id="cart-item-' . $uniqueId . '">'
                    . '<span class="discount-currency left">'.$currencySymbol.'</span>'
                    . '<input type="text" salesrep-cartid="' . $uniqueId . '" salesrep-type="price" name="lines[' . $key . '][' . $uniqueId . '][' . $index . ']" min-value="' . $minPrice . '" base-value="' . $rulePrice . '" orig-value="' . $price . '" web-price-value="' . $basePrice . '" value="' . $price . '" size="12" title="' . __('Price') . '" class="input-text price lines_price no_update disabled" maxlength="20" />'
                    . '<input class="sr_base_price" type="hidden" value="' . $basePrice . '" name="lines[' . $key . '][' . $uniqueId . '][sr_base_price]"/>'
                    . '<p>' . '<span class="left">'.__('Discount').'</span>' . '<input type="text" salesrep-cartid="' . $uniqueId . '" salesrep-type="discount" name="lines[' . $key . '][' . $uniqueId . '][discount]" max-value="' . $maxDiscount . '" orig-value="' . $discountPercent . '" value="' . $discountPercent. '" size="4" title="' . __('Discount') . '" class="input-text discount disabled" maxlength="12" /><span class="discount-percentage">%</span></p>'
                    . $resetLink
                    . '<span class="lines_price_display" style="display:none"></span>
                </div>
';
            } else {
                $html = parent::render($row);
            }
        } else {
            $html = parent::render($row);
        }

        return $html;
    }
    
   public function getGroupSequence($row){
        if(empty($row)){
            return false;
        }
        $grp_sequence= '';
        $attributes = $row->getAttributes();
        if ($attributes) {
            $attributeData = $attributes->getasarrayAttribute();
            foreach ($attributeData as $attribute) {
                if ($attribute['description'] == 'groupSequence') {
                     if(isset($attribute['value']) && $attribute['value']!= null){
                         $grp_sequence = $attribute['value'];
                            break;
                     }
                }
            }
        }
        return $grp_sequence;
    }
    
}
