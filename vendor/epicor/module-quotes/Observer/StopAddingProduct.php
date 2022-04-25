<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Observer;

//class StopAddingProduct extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
class StopAddingProduct extends AbstractObserver
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Epicor\Quotes\Helper\Data $quotesHelper
    ) {
        parent::__construct($checkoutSession, $registry, $salesOrderFactory, $quotesQuoteFactory, $quotesHelper);
        
        $this->messageManager = $messageManager;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $item = $observer->getEvent()->getItem();
        /* @var $item Mage_Sales_Model_Quote_Item */
        $quote = $item->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */
        if (!$quote) {
            return;
        }
        
        if ($quote->getItemsCount() == 0) {
            $quote->setEccQuoteId(null);
        } else if ($quote->getAllowSaving() !== true && $quote->hasEccQuoteId() && ($item->isObjectNew() || $item->getOrigData('qty') != $item->getQty())) {
           $this->messageManager->addError('You can\'t add products while you have a quote in the basket');
           $quote->setHasError(true);
            if (!$this->registry->registry('quote_session_error_set')) {
                $this->registry->register('quote_session_error_set', true);
            }
            throw new \Exception('Can\'t save to basket while basket contains a quote');
        }
    }

}