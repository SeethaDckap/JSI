<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin\Carrier;

class Flatrate
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;


    public function __construct(
        \Magento\Framework\Registry $registry
    )
    {
        $this->registry = $registry;
    }


    /**
     * Check for free shipping and reduce shipping amount
     *
     * @param Flatrate $subject
     * @param Result $result
     * @param $request
     * @return Result
     */

    //https://github.com/magento/magento2/issues/16388
    //https://github.com/magento/magento2/issues/14206
    //There are lot of issues in this area. We fixed only for flatrate.
    public function afterCollectRates(\Magento\OfflineShipping\Model\Carrier\Flatrate $subject, $result, $request)
    {
        $rate = $result->getRatesByCarrier('flatrate')[0];
        if ($rate->getPrice() && $request->getFreeShipping()) {
            $this->registry->unregister('flat_rate_applied_zero');
            $this->registry->register('flat_rate_applied_zero', 0);
            $rate->setPrice(0);
        } else {
            if ($request->getAllItems()) {
                foreach ($request->getAllItems() as $item) {
                    $quotes = $item->getQuote();
                }
                if($quotes->getShippingAddress()->getShippingMethod() =="flatrate_flatrate") {
                    $rate->setPrice($rate->getPrice());
                    $this->registry->unregister('flat_rate_applied_zero');
                    $this->registry->register('flat_rate_applied_zero', $rate->getPrice());
                }
            }
        }
        return $result;
    }
}