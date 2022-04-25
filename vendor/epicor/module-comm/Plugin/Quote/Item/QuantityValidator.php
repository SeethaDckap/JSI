<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Quote\Item;

/**
 * Class QuantityValidator
 */
class QuantityValidator
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->registry    = $registry;
        $this->scopeConfig = $scopeConfig;
    }
    // End _construct().

    /**
     * By passing Qty validation.
     *
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @return array $result
     */
    public function aroundValidate(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        $result = true;
        if ($this->isQuantityValidatorEnable() && !$this->registry->registry('QuantityValidatorObserver')) {
            $result = $proceed($observer);
        }
        return $result;
    }

    /**
     * Checking Qty validation Status
     *
     * @return boolean
     */
    private function isQuantityValidatorEnable()
    {
        return $this->scopeConfig->isSetFlag('cataloginventory/options/enable_qty_validator',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
