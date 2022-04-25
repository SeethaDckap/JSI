<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\View;


class AbstractBlock extends \Magento\Framework\View\Element\Template
{

    private $_quote;
    private $_quoteNoteType;
    private $_singleNoteType;
    private $_lineNoteType;

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


    /**
     * 
     * @return \Epicor\Quotes\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->registry->registry('quote');
        }

        return $this->_quote;
    }

    /**
     * 
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->getQuote()->getCustomer();
    }

    public function getAcceptUrl()
    {
        return $this->getUrl('epicor_quotes/manage/accept', array('id' => $this->getQuote()->getId()));
    }

    public function getRejectUrl()
    {
        return $this->getUrl('epicor_quotes/manage/reject', array('id' => $this->getQuote()->getId()));
    }

    /**
     * 
     * @return string
     */
    public function getQuoteNoteType()
    {
        if (empty($this->_quoteNoteType)) {
            $this->_quoteNoteType = $this->scopeConfig->getValue('epicor_quotes/notes/quote_note_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return $this->_quoteNoteType;
    }

    /**
     * 
     * @return string
     */
    public function getLineNoteType()
    {
        if (empty($this->_lineNoteType)) {
            $this->_lineNoteType = $this->scopeConfig->getValue('epicor_quotes/notes/line_note_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return $this->_lineNoteType;
    }

    /**
     * 
     * @return string
     */
    public function getSingleNoteType()
    {
        if (empty($this->_singleNoteType)) {
            $this->_singleNoteType = $this->scopeConfig->getValue('epicor_quotes/notes/single_note_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return $this->_singleNoteType;
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
