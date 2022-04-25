<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin\Carrier;

class Ups
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
    public function afterCollectRates(\Magento\Ups\Model\Carrier $subject, $result, $request)
    {
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                $quotes = $item->getQuote();
            }
            $upsString = "ups";
            if(preg_match("/{$upsString}/i", $quotes->getShippingAddress()->getShippingMethod())) {
                $arrayVals = $result->asArray();
                $splitMethod = explode("_",$quotes->getShippingAddress()->getShippingMethod());
                $selectedShip ='';
                if(!empty($splitMethod)) {
                    $selectedShip = $splitMethod[1];
                }
                $createKeyVals = $this->multiKeyExists($arrayVals,$selectedShip);
                $this->registry->unregister('flat_rate_applied_zero');
                $this->registry->register('flat_rate_applied_zero', $createKeyVals);
            }
        }
        return $result;
    }


    public function multiKeyExists(array $arr, $key) {
        // is in base array?
        if (array_key_exists($key, $arr)) {
            return true;
        }
        // check arrays contained in this array
        foreach ($arr as $keyss => $element) {
            if (is_array($element)) {
                if ($this->multiKeyExists($element, $key)) {
                    foreach ($element as $wholeKey=> $splittedValues) {
                        if(array_key_exists($key,$element)) {
                            return $element[$key]['price'];
                        }
                    }
                }
            }
        }
        return false;
    }

}