<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Bundle\Product;


/**
 * Bundle product price override
 *
 * To cope with teir prices being fixed for fixed price bundles
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Price extends \Magento\Bundle\Model\Product\Price
{

    /**
     * Apply tier price for bundle
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @param   decimal $qty
     * @param   decimal $finalPrice
     * @return  decimal
     */
    protected function _applyTierPrice($product, $qty, $finalPrice)
    {
        if (is_null($qty)) {
            return $finalPrice;
        }

        if ($product->getPriceType() == 0) {
            return parent::_applyTierPrice($product, $qty, $finalPrice);
        } else {
            $tierPrice = $product->getTierPrice($qty);

            if (is_numeric($tierPrice)) {
                $finalPrice = min($finalPrice, $tierPrice);
            }

            return $finalPrice;
        }
    }

    /**
     * Get product tier price by qty
     *
     * @param   decimal $qty
     * @param   \Magento\Catalog\Model\Product $product
     * @return  decimal
     */
    public function getTierPrice($qty = null, $product)
    {
        $allGroups = \Magento\Customer\Model\Group::CUST_GROUP_ALL;
        $prices = $product->getData('tier_price');

        if (is_null($prices)) {
            if ($attribute = $product->getResource()->getAttribute('tier_price')) {
                $attribute->getBackend()->afterLoad($product);
                $prices = $product->getData('tier_price');
            }
        }

        if (is_null($prices) || !is_array($prices)) {
            if (!is_null($qty)) {
                return $product->getPrice();
            }
            return array(array(
                'price' => $product->getPrice(),
                'website_price' => $product->getPrice(),
                'price_qty' => 1,
                'cust_group' => $allGroups
            ));
        }

        $custGroup = $this->_getCustomerGroupId($product);
        if ($qty) {
            $prevQty = 1;
            $prevPrice = ($product->getPriceType() == 1) ? $product->getPrice() : 0;
            $prevGroup = $allGroups;

            foreach ($prices as $price) {
                if ($price['cust_group'] != $custGroup && $price['cust_group'] != $allGroups) {
                    // tier not for current customer group nor is for all groups
                    continue;
                }
                if ($qty < $price['price_qty']) {
                    // tier is higher than product qty
                    continue;
                }
                if ($price['price_qty'] < $prevQty) {
                    // higher tier qty already found
                    continue;
                }
                if ($price['price_qty'] == $prevQty && $prevGroup != $allGroups && $price['cust_group'] == $allGroups) {
                    // found tier qty is same as current tier qty but current tier group is ALL_GROUPS
                    continue;
                }

                if ($product->getPriceType() == 1 && $price['website_price'] < $prevPrice) {
                    $prevPrice = $price['website_price'];
                    $prevQty = $price['price_qty'];
                    $prevGroup = $price['cust_group'];
                } else if ($product->getPriceType() != 1 && $price['website_price'] > $prevPrice) {
                    $prevPrice = $price['website_price'];
                    $prevQty = $price['price_qty'];
                    $prevGroup = $price['cust_group'];
                }
            }

            return $prevPrice;
        } else {
            $qtyCache = array();
            foreach ($prices as $i => $price) {
                if ($price['cust_group'] != $custGroup && $price['cust_group'] != $allGroups) {
                    unset($prices[$i]);
                } else if (isset($qtyCache[$price['price_qty']])) {
                    $j = $qtyCache[$price['price_qty']];
                    if ($prices[$j]['website_price'] < $price['website_price']) {
                        unset($prices[$j]);
                        $qtyCache[$price['price_qty']] = $i;
                    } else {
                        unset($prices[$i]);
                    }
                } else {
                    $qtyCache[$price['price_qty']] = $i;
                }
            }
        }

        return ($prices) ? $prices : array();
    }

}
