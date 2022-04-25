<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Model;
/**
 * Elements Payment  
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */



class Elements extends \Magento\Framework\Model\AbstractModel
{


    /** @var \Epicor\Elements\Model\Api */
    protected $_api;
    
    /** @var  \Magento\Sales\Model\Order */
    protected $_order;
    
    /** @var \Magento\Quote\Model\Quote */
    protected $_quote;
    
    /**
     * @var \Epicor\Elements\Model\Api
     */
    protected $elementsApi;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;
    
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteQuoteFactory;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;



    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Elements\Model\Api $elementsApi,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Magento\Quote\Model\QuoteFactory $quoteQuoteFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->elementsApi = $elementsApi;
        $this->scopeConfig = $scopeConfig;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->quoteQuoteFactory = $quoteQuoteFactory;
        $this->urlBuilder = $urlBuilder;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }    

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Epicor\Elements\Model\ResourceModel\Elements');
    }

    
}