<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Crq;

class UpdateLinesForSalesRepPrices extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\SalesRep\Block\Crqs\Details\Lines\Renderer\CurrencyFactory
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $salesRepHelper = $this->salesRepHelper;

        $action = $this->request->getActionName();

        if (!$salesRepHelper->isEnabled() || $action == 'duplicate') {
            return;
        }

        $isSalesRep = $this->customerSession->getCustomer()->isSalesRep();

        if ($isSalesRep && $this->registry->registry('rfqs_editable')) {
            $lines = $observer->getEvent()->getLines();

            $rfqHelper = $this->customerconnectRfqHelper;

            $products = array();

            foreach ($lines->getData() as $line) { 
                $check_Is_ConfiguratorProduct = $this->IsConfiguratorProduct($line);
                $product = $this->customerconnectMessagingHelper->getProductObject((string) $line->getProductCode(), $check_Is_ConfiguratorProduct);

                if ($product->getTypeId() == 'grouped' && $product->getEccDefaultUom() != $line->getUnitOfMeasureCode()) {
                    $uomSku = $line->getProductCode() . $this->salesRepHelper->getUOMSeparator() . $line->getUnitOfMeasureCode();
                    $uomProduct = $this->customerconnectMessagingHelper->getProductObject((string) $uomSku);
                    $product = (empty($uomProduct->getData())) ? $product : $uomProduct;
                }
                if ($product->getEccConfigurator()) {
                    $msqAtts = array(
                        'groupSequence' => $rfqHelper->getRfqLineGroupSequence($line)
                    );

                    $product->setMsqAttributes($msqAtts);
                }

                $product->setLineQty($line->getQuantity());
                $product->setQty($line->getQuantity());

                $products[] = $product;
            }

            if (!empty($products)) {
                $rfqHelper->sendMsqForRfqProducts($products);
            }
        }
    }
    
    /*
     * To check if line product is  ERP Configurator Part type product
     * return boolean
     */
    protected function IsConfiguratorProduct($line){
        if(empty($line)){
            return false;
        }
        $is_configurator = false;
        $attributes = $line->getAttributes();
        if ($attributes) {
            $attributeData = $attributes->getasarrayAttribute();
            foreach ($attributeData as $attribute) {
                if ($attribute['description'] == 'ewaSku') {
                     if(isset($attribute['value']) && $attribute['value']!= null){
                            $is_configurator = true;
                            break;
                     }
                }
            }
        }
        return $is_configurator;
    }
}