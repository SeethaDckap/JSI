<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Bsv;

use Epicor\Comm\Model\RepriceFlag as RepriceFlag;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

class UpdateBsvRequestPrices extends AbstractObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /* @var $salesRepHelper \Epicor\SalesRep\Helper\Data */
        $salesRepHelper = $this->salesRepHelper;

        if (!$salesRepHelper->isEnabled()) {
            return;
        }

        $isSalesRep = $this->customerSession->getCustomer()->isSalesRep();

        if ($isSalesRep == false) {
            return;
        }

        /* @var $bsv \Epicor\Comm\Model\Message\Request\Bsv */
        $bsv = $observer->getEvent()->getMessage();
        $data = $bsv->getMessageArray();
        /* @var $helper \Epicor\SalesRep\Helper\Pricing\Rule\Product */
        $helper = $this->salesRepPricingRuleProductHelper;

        if (isset($data['messages']['request']['body']['lines']['line']) && is_array($data['messages']['request']['body']['lines']['line'])) {
            $bGoodstotal = 0;
            $bGoodstotalInc = 0;
            $totalsRecalculation = false;
            foreach ($data['messages']['request']['body']['lines']['line'] as &$line) {
                $itemId = $line['_attributes']['itemId'];
                $item = $bsv->getLineItem($itemId);
                if ($item) {
                    $quantity = $line['quantity'];
                    if (!is_null($item->getEccSalesrepPrice())) {
                        $totalsRecalculation = true;
                        //Get Item Product Prices & Qty
                        $origPrice = !is_null($item->getEccOriginalPrice()) ? $item->getEccOriginalPrice() : $item->getBasePrice();
                        $salesrepItemPrice = $item->getEccSalesrepPrice();
                        $update = false;

                        $product = $item->getProduct();
                        $product->setEwaSku($item->getOptionByType('ewa_sku'));
                        $product->setEccMsqBasePrice($item->getEccMsqBasePrice());
                        $rulePrice = $helper->getRuleBasePrice($product, $origPrice, $quantity);
                        $minPrice = $helper->getMinPrice($product, $origPrice, $quantity);

                        // Check if sales rep has bypassed
                        if ($item instanceof Item) {
                            if ($item->getEccOriginalPrice() != $item->getOrigData('ecc_original_price') || $item->getEccSalesrepRulePrice() != $rulePrice) {
                                $minPrice = $origPrice;
                                $repDiscount = $helper->getDiscountAmount($origPrice, $rulePrice);
                                $update = true;
                                $message = __('%1 base price has changed', $product->getName());
                            } else if ($minPrice > $salesrepItemPrice && $item->getEccOriginalPrice() != $salesrepItemPrice) {
                                $minPrice = $helper->getMinPrice($product, $origPrice);
                                $repDiscount = $helper->getMaxDiscount($product, $origPrice);
                                $update = true;
                                $message = __('%1 sales rep discount was greater than allowed', $product->getName());
                            }
                        }

                        if ($update) {
                            $this->messageManager->addNoticeMessage($message);
                            $salesrepItemPrice = $minPrice;
                            $item->setEccSalesrepRulePrice($rulePrice);
                            $item->setEccSalesrepPrice($salesrepItemPrice);
                            $item->setEccSalesrepDiscount($repDiscount);
                            $item->save();
                        }

                        $salesrepItemPriceInc = $item->getPriceInclTax();
                        $salesrepLineTotal = $salesrepItemPrice * $quantity;
                        $itemOrigData = $item->getOrigData();
                        $itemData = $item->getData();
                        if ((isset($itemData['qty']) && isset($itemOrigData['qty']) && $itemData['qty'] != $itemOrigData['qty']) || ($salesrepItemPrice != $origPrice)) {
                            $salesrepItemPriceInc = $salesrepItemPrice;
                            $salesrepLineTotalInc = $salesrepLineTotal;
                        } else {
                            $salesrepLineTotalInc = $salesrepItemPriceInc * $quantity;
                        }
                        $itemDiscount         = ( $origPrice - $salesrepItemPrice );
                        $lineDiscount         = ( $itemDiscount * $quantity );
                        $line['price']        = $salesrepItemPrice;
                        $line['priceInc']     = $salesrepItemPriceInc;
                        $line['lineValue']    = $salesrepLineTotal;
                        $line['lineValueInc'] = $salesrepLineTotalInc;
                        $line['lineDiscount'] = $lineDiscount;
                        $line['_attributes']['preventRepricing'] = RepriceFlag::getItemRepricingFlag(
                            $lineDiscount,
                            $quantity,
                            $line['_attributes']['preventRepricing']
                        );

                        $salesrepItemPriceInc = $item->getPriceInclTax();
                        $salesrepLineTotal    = ( $salesrepItemPrice * $quantity );
                        $bGoodstotal         += $salesrepLineTotal;
                        $bGoodstotalInc      += $salesrepLineTotalInc;

                    } else {
                        $origPrice = !is_null($item->getEccOriginalPrice()) ? $item->getEccOriginalPrice() : $item->getBasePrice();
                        $product   = $item->getProduct();
                        if (!$product->getEccMsqBasePrice()) {
                            $product->setEccMsqBasePrice($item->getEccMsqBasePrice());
                        }
                        $rulePrice       = $helper->getRuleBasePrice($item->getProduct(), $origPrice, $quantity);
                        $discountedPrice = $item->getPrice();
                        $discountPercent = $helper->getDiscountAmount($discountedPrice, $rulePrice);
                        $item->setEccSalesrepDiscount($discountPercent);
                        $item->setEccSalesrepRulePrice($rulePrice);
                        $item->save();
                        $salesrepItemPriceInc = $item->getPriceInclTax();
                        $salesrepLineTotal    = $item->getPrice() * $quantity;
                        $salesrepItemPrice    = $item->getPrice();
                        $itemOrigData         = $item->getOrigData();
                        $itemData             = $item->getData();
                        if ((isset($itemData['qty']) && isset($itemOrigData['qty']) && $itemData['qty'] != $itemOrigData['qty']) || ($salesrepItemPrice != $origPrice)) {
                            $salesrepItemPriceInc = $salesrepItemPrice;
                            $salesrepLineTotalInc = $salesrepLineTotal;
                        } else {
                            $salesrepLineTotalInc = ( $salesrepItemPriceInc * $quantity );
                        }

                        $itemDiscount = ( $origPrice - $salesrepItemPrice );
                        $lineDiscount = ( $itemDiscount * $quantity );

                        $bGoodstotal       += $salesrepLineTotal;
                        $bGoodstotalInc    += $salesrepLineTotalInc;
                    }
                }
            }

            if ($totalsRecalculation) {
                $data = $this->setUpdateTotal($data, $bGoodstotal, $bGoodstotalInc);
            }
        }
        $bsv->setMessageArray($data);
    }

    /**
     * @param $data
     * @param $bGoodstotal
     * @param $bGoodstotalInc
     * @return mixed
     */
    private function setUpdateTotal($data, $bGoodstotal, $bGoodstotalInc)
    {
        $charge = $data['messages']['request']['body']['order']['carriageAmount'];
        $chargeInc = $data['messages']['request']['body']['order']['carriageAmountInc'];
        $calculateDiscount = $data['messages']['request']['body']['order']['discountAmount'];
        $bgrandTotal = $bGoodstotal + $charge - $calculateDiscount;
        $bgrandTotalInc = $bGoodstotalInc + $chargeInc - $calculateDiscount;

        $data['messages']['request']['body']['order']['goodsTotal'] = $bGoodstotal ?: 0;
        $data['messages']['request']['body']['order']['goodsTotalInc'] = $bGoodstotalInc ?: 0;
        $data['messages']['request']['body']['order']['grandTotal'] = $this->checkValueDataType($bgrandTotal);
        $data['messages']['request']['body']['order']['grandTotalInc'] = $this->checkValueDataType($bgrandTotalInc);

        return $data;
    }

    /**
     * Convert array value from Integer to String
     *
     * @param null $value
     * @return string|null
     */
    private function checkValueDataType($value = null)
    {
        if($value == null || $value == 0){
            return '0';
        }else{
            return $value;
        }
    }
}
