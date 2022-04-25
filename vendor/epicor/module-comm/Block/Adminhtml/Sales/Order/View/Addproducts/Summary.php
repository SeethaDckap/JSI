<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Sales\Order\View\Addproducts;


class Summary extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->storeManager = $context->getStoreManager();
        $this->_localeResolver = $localeResolver;
        parent::__construct(
            $context,
            $data
        );

        $this->setData($this->registry->registry('return_data'));
        $this->setData('products', $this->registry->registry('products'));
        $this->setTemplate('epicor_comm/sales/order/view/addproduct/summary.phtml');
    }

    public function formatPrice($price)
    {
        $currency_code = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $rate = $this->storeManager->getStore()->getBaseCurrency()->getRate($this->storeManager->getStore()->getCurrentCurrencyCode());
        $data = floatval($price) * $rate;
        $data = sprintf("%f", $data);
        //M1 > M2 Translation Begin (Rule p2-6.4)
        //return Mage::app()->getLocale()->currency($currency_code)->toCurrency($data);
        return $this->_localeResolver->getLocale()->currency($currency_code)->toCurrency($data);
        //M1 > M2 Translation End
    }

}
