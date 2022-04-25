<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\View;


class Quoteinfo extends \Epicor\Quotes\Block\View\AbstractBlock
{


    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Epicor\Quotes\Helper\Data
     */
    protected $quotesHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Quotes\Helper\Data $quotesHelper,
        array $data = [])
    {
        $this->customerSession = $customerSession;
        $this->quotesHelper = $quotesHelper;
        parent::__construct($context, $registry, $data);
    }


    public function getExpires()
    {
        return $this->quotesHelper->getHumanExpires($this->getQuote());
    }

    public function getUpdateUrl()
    {
        return $this->getUrl('epicor_quotes/manage/update', array('id' => $this->getQuote()->getId()));
    }

    public function getDuplicateUrl()
    {
        return $this->getUrl('epicor_quotes/manage/saveDuplicate', array('id' => $this->getQuote()->getId()));
    }

    public function getReSubmitUrl()
    {
        return $this->getUrl('epicor_quotes/manage/resubmit', array('id' => $this->getQuote()->getId()));
    }

    public function getUpdatedAt()
    {
        return $this->quotesHelper->getLocalDate($this->getQuote()->getUpdatedAt());
    }

    public function allowGlobalTickbox()
    {
        $customerGlobal = $this->scopeConfig->isSetFlag('epicor_quotes/general/allow_customer_global', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $customer = $this->customerSession->getCustomer();
        /* @var $customer Epicor_Comm_Model_Customer */#

        $allowGlobal = false;

        if ($customer->isCustomer() && $customerGlobal) {
            $allowGlobal = true;
        }

        return $allowGlobal;
    }

    public function showCreatedBy()
    {
        return $this->getQuote()->getIsGlobal();
    }

}
