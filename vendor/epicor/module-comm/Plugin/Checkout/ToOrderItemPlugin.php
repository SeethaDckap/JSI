<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Checkout;

/**
 * One page checkout processing model
 */
class ToOrderItemPlugin
{

    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }


    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem Item */
        $orderItem = $proceed($item, $additional);
        $orderItem->setEccLineComment($item->getEccLineComment());
        $orderItem->setEccOriginalPrice($item->getEccOriginalPrice());
        
         $orderItem->setEccBsvPrice($item->getEccBsvPrice());
         $orderItem->setEccBsvPriceInc($item->getEccBsvPriceInc());
         $orderItem->setEccBsvLineValue($item->getEccBsvLineValue());
         $orderItem->setEccBsvLineValueInc($item->getEccBsvLineValueInc());
         $orderItem->setEccLineComment($item->getEccLineComment());
         $orderItem->setEccRequiredDate($item->getEccRequiredDate());
         $orderItem->setEccDeliveryDeferred($item->getEccDeliveryDeferred());
         $orderItem->setEccLocationCode($item->getEccLocationCode());
         $orderItem->setEccLocationName($item->getEccLocationName());
         $orderItem->setEccGqrLineNumber($item->getEccGqrLineNumber());
         $orderItem->setEccContractCode($item->getEccContractCode());
         $orderItem->setEccSalesrepPrice($item->getEccSalesrepPrice());
         $orderItem->setEccSalesrepDiscount($item->getEccSalesrepDiscount());
         $orderItem->setEccSalesrepRulePrice($item->getEccSalesrepRulePrice());
         
        
        
        return $orderItem;
    }
}