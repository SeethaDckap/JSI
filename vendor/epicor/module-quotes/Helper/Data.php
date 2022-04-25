<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Helper;


use Epicor\Common\Helper\Context;

class Data extends \Epicor\Common\Helper\Data
{

    /**
     * @var \Epicor\Quotes\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quotesResourceQuoteCollectionFactory;

    protected $checkoutSession;
    
    protected $cartHelper;
    
    protected $response;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Epicor\Quotes\Model\ResourceModel\Quote\CollectionFactory $quotesResourceQuoteCollectionFactory,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Response\Http $response,
        Context $context,
        \Epicor\AccessRight\Helper\Data $authorization
    )
    {
        $this->checkoutSession = $context->getCheckoutSession();
        $this->cartHelper = $cartHelper;
        $this->response = $response;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->quotesResourceQuoteCollectionFactory = $quotesResourceQuoteCollectionFactory;
        $this->_accessauthorization = $authorization->getAccessAuthorization();

        parent::__construct($context);
    }


    public function isCartToQuoteActive()
    {
        if (!$this->_accessauthorization->isAllowed('Epicor_Checkout::checkout_checkout_cart_quote')) {
            return false;
        }
        return true;
    }

    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     *
     * @param \Epicor\Comm\Model\Customer $customer
     */
    public function getCustomerQuotes()
    {
        $customer = $this->customerSessionFactory->create()->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        $collection = $this->quotesResourceQuoteCollectionFactory->create();
        /* @var $collection \Epicor\Quotes\Model\ResourceModel\Quote\Collection */

        $collection->joinQuoteCustomerTable()
            ->filterByCustomer($customer)
            ->addOrder('created_at', 'desc')
            ->load();

        return $collection;
    }

    /**
     *
     * @param \Epicor\Quotes\Model\Quote $quote
     * @return string
     */
    public function getHumanExpires($quote)
    {
        //  $str = $this->getLocalDate($quote->getExpires(), self::DAY_FORMAT_MEDIUM);        // removed as locale was being applied and it shouldn't be
        $str = $this->convertIso8601DateNoLocale($quote->getExpires(), 'M j, Y');
        if ($quote->isActive()) { 
            $humantime = $this->readableTimeDiff(
                $quote->getExpires(), false, \Epicor\Quotes\Helper\Data::READABLE_TO_DAYS
            ); 
            if (is_array($humantime) && $humantime[0] == '0') {  
                $humantime = $this->__('today');
            }  
            $str .= ' (' . $humantime . ')';
        } else if ($quote->getStatusId() != \Epicor\Quotes\Model\Quote::STATUS_QUOTE_EXPIRED) {
            $str = 'N/A';
        }

        return $str;
    }

    public function isQuotesEnabledForCustomer()
    {
        $helper = $this->commHelper;
        /* @var $helper \Epicor\Comm\Helper\Data */

        $enabled = ($helper->isFunctionalityDisabledForCustomer('prices')) ? false : true;

        if ($enabled) {
            $enabled = $this->scopeConfig->isSetFlag('epicor_quotes/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return $enabled;
    }
    // Get System config value Scope vise
    /* Allowed scopes 
     *  SCOPE_STORES = 'stores';
        SCOPE_WEBSITES = 'websites';
        SCOPE_STORE   = 'store';
        SCOPE_GROUP   = 'group';
        SCOPE_WEBSITE = 'website';
     */
    public function getConfig($config_path, $scope=\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
                $config_path,$scope);
    }
    
    public  function getTotalItemsCount()
    {
        if ($this->cartHelper->getItemsCount() === 0) {
            $url = $this->urlBuilder->getUrl('checkout/cart');
            $this->response->setRedirect($url);
        }        
    }    
    
}
