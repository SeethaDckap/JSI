<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block;


class Managelist extends \Magento\Framework\View\Element\Template
{

    const FRONTEND_RESOURCE_ACCOUNT_QUOTES_DETAILS = "Epicor_Customer::my_account_quotes_details";

    const FRONTEND_RESOURCE_ACCOUNT_QUOTES_REJECT = "Epicor_Customer::my_account_quotes_reject";

    const FRONTEND_RESOURCE_ACCOUNT_QUOTES_DUPLICATE = "Epicor_Customer::my_account_quotes_duplicate";

    private $_quotes;
    /**
     * @var \Epicor\Quotes\Helper\Data
     */
    protected $quotesHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Quotes\Helper\Data $quotesHelper,
        array $data = []
    ) {
        $this->quotesHelper = $quotesHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $data
        );
    }


    public function getCustomerQuotes()
    {
        if (!$this->_quotes) {
            $this->_quotes = $this->quotesHelper->getCustomerQuotes();
        }
        return $this->_quotes;
    }

    public function getViewUrl($quote_id)
    {
        return $this->getUrl('epicor_quotes/manage/view', array('id' => $quote_id));
    }

    public function getRejectUrl($quote_id)
    {
        return $this->getUrl('epicor_quotes/manage/reject', array('id' => $quote_id));
    }

    public function getDuplicateUrl($quote_id)
    {
        return $this->getUrl('epicor_quotes/manage/saveDuplicate', array('id' => $quote_id, 'req' => 'dup'));
    }

    public function isAllowed($code)
    {
        return $this->_accessauthorization->isAllowed($code);
    }

    public function isDetailsAllowed()
    {
        return $this->isAllowed(static::FRONTEND_RESOURCE_ACCOUNT_QUOTES_DETAILS);
    }

    public function isRejectAllowed()
    {
        return $this->isAllowed(static::FRONTEND_RESOURCE_ACCOUNT_QUOTES_REJECT);
    }

    public function isDuplicateAllowed()
    {
        return $this->isAllowed(static::FRONTEND_RESOURCE_ACCOUNT_QUOTES_DUPLICATE);
    }

    public function isActionAllowed()
    {
        return $this->isDetailsAllowed() || $this->isRejectAllowed() || $this->isDuplicateAllowed();
    }



}
