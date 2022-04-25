<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes;

class Updatetotals extends \Epicor\Quotes\Controller\Adminhtml\Quotes\Quotes
{

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Epicor\Quotes\Helper\Data
     */
    protected $quotesHelper;
    /*
    public function __construct(
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Epicor\Quotes\Helper\Data $quotesHelper
    ) {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->quotesHelper = $quotesHelper;
    } */
    
    
     public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
         \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
         \Epicor\Quotes\Helper\Data $quotesHelper    
        )
    {
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->quotesHelper = $quotesHelper;
        
        $this->backendHelper = $context->getHelper();
        $this->registry = $context->getRegistry();
           
        parent::__construct($context, $backendAuthSession, $quotesQuoteFactory);
    }
    
    public function execute()
    {
        $errorMsg = __('Error occurred while trying to calculate totals.');
        $error = true;
        $subtotal = 0;
        $taxTotal = 0;
        $products = array();
        if ($this->getRequest()->getPost('prices')) {
            try {
                $quote = $this->quotesQuoteFactory->create()->load($this->getRequest()->get('id'));
                /* @var $quote Epicor_Quotes_Model_Quote */
                $currencyCode = $quote->getcurrencyCode();
                $prices = json_decode($this->getRequest()->getPost('prices'), true);
                $qtys = json_decode($this->getRequest()->getPost('qtys'), true);

                foreach ($quote->getProducts() as $product) {
                    /* @var $product Epicor_Quotes_Model_Quote_Product */
                    $price = $prices[$product->getId()] * $qtys[$product->getId()];
                    $taxTotal += $quote->getProductTax($product->getProduct(), $price);
                    $subtotal += $price;
                    $products[$product->getId()] = $this->quotesHelper->formatPrice($price, true, $currencyCode);
                }
                $error = false;
                $errorMsg = '';
            } catch (\Exception $e) {
                $error = true;
                 $this->messageManager->addError($errorMsg);
            }
        }

        $this->getResponse()->setBody(
            json_encode(
                array(
                    'error' => $error,
                    'errorMsg' => $errorMsg,
                    'products' => $products,
                    'subtotal' => $this->quotesHelper->formatPrice($subtotal, true, $currencyCode),
                    'taxTotal' => $this->quotesHelper->formatPrice($taxTotal, true, $currencyCode),
                    'grandTotal' => $this->quotesHelper->formatPrice($subtotal + $taxTotal, true, $currencyCode),
                )
            )
        );
    }

    }
