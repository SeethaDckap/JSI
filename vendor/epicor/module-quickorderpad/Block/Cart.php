<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Block;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Cart extends \Magento\Checkout\Block\Cart
{

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Epicor\Comm\Helper\Data $commHelper,
        array $data = []
    )
    {
        $this->commHelper = $commHelper;
        $this->storeManager = $context->getStoreManager();
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrlBuilder,
            $cartHelper,
            $httpContext,
            $data
        );
        $quote = $this->getQuote();
        if (!$quote->validateMinimumAmount($quote->getIsMultiShipping())) {
            $amount = $this->commHelper->getMinimumOrderAmount($quote->getCustomer()->getEccErpaccountId());
            $_fromCurr = $quote->getBaseCurrencyCode();
            $_toCurr = $this->storeManager->getStore()->getCurrentCurrencyCode();
            $minimumAmount = $this->commHelper->getCurrencyConvertedAmount($amount, $_fromCurr, $_toCurr);
            //M1 > M2 Translation Begin (Rule 55)
            //$warning = $this->scopeConfig->getValue('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : __('Minimum order amount is %s', $minimumAmount);
            $warning = $this->scopeConfig->getValue('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? $this->scopeConfig->getValue('sales/minimum_order/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : __('Minimum order amount is %1', $minimumAmount);
            //M1 > M2 Translation End
            $this->setWarning($warning);
        }
    }

    public function chooseTemplate()
    {
        $itemsCount = $this->getItemsCount() ? $this->getItemsCount() : $this->getQuote()->getItemsCount();
        if ($itemsCount) {
            $this->setTemplate($this->getCartTemplate());
        } else {
            $this->setTemplate($this->getEmptyTemplate());
        }
    }

    //M1 > M2 Translation Begin (Rule 56)
    /**
     * @param $path
     * @return mixed
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $path
     * @return bool
     */
    public function getConfigFlag($path)
    {
        return $this->_scopeConfig->isSetFlag($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    //M1 > M2 Translation End
}
