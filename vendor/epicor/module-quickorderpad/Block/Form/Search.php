<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Block\Form;


class Search extends \Magento\Framework\View\Element\Template
{

    private $_url = '/quickorderpad/form/results';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
    }


    public function getQuery()
    {
        return $this->registry->registry('search-query');
    }

    public function getInstock()
    {
        return $this->registry->registry('search-instock');
    }

    public function getSearchUrl()
    {
        return $this->_url;
    }

    public function setSearchUrl($url)
    {
        $this->_url = $url;
    }

    public function showOnlyInStockTickbox()
    {
        $showOnlyInTickbox = false;
        if (!$this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/msq_request/products_always_in_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $showOnlyInTickbox = true;
        }
        return $showOnlyInTickbox;
    }

}
