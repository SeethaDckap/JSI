<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * Catalog grouped product info block
 */

namespace Epicor\Comm\Block\Catalog\Product\View\Type\Grouped;

class View extends \Magento\GroupedProduct\Block\Product\View\Type\Grouped
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        array $data = []
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $context->getStoreManager();
        parent::__construct(
            $context, $arrayUtils, $data
        );
    }

    public function getAssociatedProducts()
    {
        $_associatedProducts = $this->getProduct()->getTypeInstance()->getAssociatedProducts($this->getProduct());
        if (!$this->isShowOutOfStock()) {
            $_associatedProducts = !empty($_associatedProducts) ? array_filter($_associatedProducts, function ($arrayValue) {
                return $arrayValue->isSaleable();
            }) : [];
        }
        return $_associatedProducts;
    }

    public function getMsqForNonErp()
    {
        return $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/msq_request/msq_for_non_erp_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Hide stock availability for
     * Non-Stock group product
     *
     * @return bool
     */
    public function displayProductStockStatus()
    {
        if ($this->getProduct() && !$this->getProduct()->getIsEccNonStock()) {
            return parent::displayProductStockStatus();
        }
        return false;
    }

    /**
     * Display out of stock products option
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $store
     * @return bool
     */
    public function isShowOutOfStock()
    {
        return $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
