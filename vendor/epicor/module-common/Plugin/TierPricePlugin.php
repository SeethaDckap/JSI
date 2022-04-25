<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Plugin;

use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;

class TierPricePlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;    

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
   ) {
       $this->scopeConfig = $scopeConfig;
   }
   
    /**
     * Adding precision value from ECC config setting
     * @param \Magento\Catalog\Pricing\Price\TierPrice $subject
     * @param \Closure $proceed
     * @param AmountInterface $amount
     * @return float
     */
    public function aroundGetSavePercent(\Magento\Catalog\Pricing\Price\TierPrice $subject, \Closure $proceed, $amount)
    {
        $proceed($amount);

        /* System config value set for the Tier Price Display */
        $precision = $this->scopeConfig->getValue('epicor_common/tier_prices/precision', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return round(
            100 - ((100 / $subject->getProduct()->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue())
                * $amount->getBaseAmount()),$precision
        );
    }
}
