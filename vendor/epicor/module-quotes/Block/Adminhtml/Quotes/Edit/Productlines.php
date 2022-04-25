<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\Adminhtml\Quotes\Edit;


class Productlines extends \Epicor\Quotes\Block\Adminhtml\Quotes\Edit\AbstractBlock
{

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\Product\CollectionFactory
     */
    protected $quotesResourceQuoteProductCollectionFactory;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;
    /**
     * @var \Epicor\Quotes\Helper\Data
     */
    protected $quotesHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Quotes\Model\ResourceModel\Quote\Product\CollectionFactory $quotesResourceQuoteProductCollectionFactory,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Quotes\Helper\Data $quotesHelper,
        array $data = [])
    {
        $this->quotesHelper = $quotesHelper;
        $this->backendHelper = $backendHelper;
        $this->quotesResourceQuoteProductCollectionFactory = $quotesResourceQuoteProductCollectionFactory;
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

    public function getUpdateTotalsUrl()
    {  
        return $this->backendHelper->getUrl("epicorquotes/quotes_quotes/updatetotals/", array('id' => $this->getQuote()->getId()));
    }

    public function getAcceptUrl()
    {
        return $this->backendHelper->getUrl("epicorquotes/quotes_quotes/accept/", array('id' => $this->getQuote()->getId()));
    }

}
