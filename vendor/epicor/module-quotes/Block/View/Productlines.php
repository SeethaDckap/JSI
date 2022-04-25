<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\View;


use Magento\Store\Model\ScopeInterface;

class Productlines extends \Epicor\Quotes\Block\View\AbstractBlock
{

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\Product\CollectionFactory
     */
    protected $quotesResourceQuoteProductCollectionFactory;
    /**
     * @var \Epicor\Quotes\Helper\Data
     */
    protected $quotesHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Quotes\Model\ResourceModel\Quote\Product\CollectionFactory $quotesResourceQuoteProductCollectionFactory,
         \Epicor\Quotes\Helper\Data $quotesHelper,   
        array $data = [])
    {
        $this->quotesResourceQuoteProductCollectionFactory = $quotesResourceQuoteProductCollectionFactory;
        $this->quotesHelper = $quotesHelper;
        parent::__construct($context, $registry, $data);
    }


    public function getProductLines()
    {
        $productlines = $this->quotesResourceQuoteProductCollectionFactory->create();
        /* @var $productlines Mage_Core_Model_Resource_Collection_Abstract */

        $productlines->addFieldToFilter('quote_id', $this->getQuote()->getId());
        return $productlines;
    }

    public function formatPrice($price, $show_currency = true, $currency_code = null)
    {
        return $this->quotesHelper->formatPrice($price, $show_currency, $currency_code);
    }

}
