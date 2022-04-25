<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ProcessFinalPrice extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $catalogRuleRuleFactoryExist=null;

    /**
     * Apply catalog price rules to product
     *
     * @param   \Magento\Framework\Event\Observer $observer
     *
     * @return  \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        /* @var $product Epicor_Comm_Model_Product */

        $qty = $observer->getEvent()->getQty();
        $rule = $this->catalogRuleRuleFactory();
        /* @var $rule Mage_Catalogrule_Model_Rule */
        $price = $product->getPrice();
        if (!empty($qty)) {
            $tierPrice = $product->getTierPrice($qty);
            if (is_array($tierPrice)) {
                $tierPrice = $tierPrice[0]['website_price'];
            }

            if (!empty($tierPrice)) {
                $price = min($price, $tierPrice);
            }
        }

        $special = $product->getSpecialPrice();
        $alreadyApplied =  $product->getStaticLocationPrice();
        if (!is_null($special)) {
            $price = min($price, $special);
        }
        $rulePrice =false;
        if(!$alreadyApplied) {
            $rulePrice = $rule->calcProductPriceRule($product, $price);
        }
        if (!$rulePrice) {
            $rulePrice = $price;
        }

        $store = $this->storeManager->getStore();
        /* @var $store Epicor_Comm_Model_Store */

        $rulePrice = $store->roundPrice($rulePrice, 4);

        $product->setFinalPrice($rulePrice);
        return $this;
    }

    public function catalogRuleRuleFactory()
    {
        if (!$this->catalogRuleRuleFactoryExist) {
            $this->catalogRuleRuleFactoryExist = $this->catalogRuleRuleFactory->create();
        }
        return $this->catalogRuleRuleFactoryExist;
    }

}