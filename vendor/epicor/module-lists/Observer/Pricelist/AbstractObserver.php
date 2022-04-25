<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Observer\Pricelist;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $listsFrontendPricelistHelper;

    protected $catalogResourceModelProductFactory;

    protected $commonHelper;

    protected $listsFrontendContractHelper;

    public function __construct(
        \Epicor\Lists\Helper\Frontend\Pricelist $listsFrontendPricelistHelper,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogResourceModelProductFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper
    ) {
        $this->listsFrontendPricelistHelper = $listsFrontendPricelistHelper;
        $this->catalogResourceModelProductFactory = $catalogResourceModelProductFactory;
        $this->commonHelper = $commonHelper;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
    }

    protected function getRepriceableProducts($products, $prices)
    {
        foreach ($products as $id => $product) {
            $sku = $this->getProductSku($product);
            if (!isset($prices[$sku])) {
                unset($products[$id]);
            }
        }

        return $products;
    }

    protected function getProductSku($product)
    {
        $sku = $this->catalogResourceModelProductFactory->create()->getAttributeRawValue($product->getId(), 'sku', 0);
        $sku = $sku['sku'];
        $productType = $product->getTypeId();
       if ($productType != 'grouped') {
           if ($productType == 'configurable') {
               $sku = (!empty($this->catalogResourceModelProductFactory->create()->getAttributeRawValue($product->getId(), 'ecc_pricing_sku', 0)))?
                   $this->catalogResourceModelProductFactory->create()->getAttributeRawValue($product->getId(), 'ecc_pricing_sku', 0):$sku;
           }
            return $sku;
        }

       return $sku . $this->commonHelper->getUOMSeparator() . $product->getEccDefaultUom();
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }

    protected function shouldUseContractPrice($product)
    {
        $contractHelper = $this->listsFrontendContractHelper;
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */

        // contracts are disabled
        if ($contractHelper->contractsDisabled()) {
            return false;
        }

        $selectedContract = $contractHelper->getSelectedContractCode();
        // no contract selected
        if (empty($selectedContract)) {
            return false;
        }

        $contract = $contractHelper->getSelectedContractModel();
        /* @var $contract Epicor_Lists_Model_ListModel */
        return $contractHelper->isProductValidForContract($contract->getId(), $product->getId());
    }

}

