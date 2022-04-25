<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Crq;

class UpdateColumnsForSalesRepPrices extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\SalesRep\Block\Crqs\Details\Lines\Renderer\CurrencyFactory
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $salesRepHelper = $this->salesRepHelper;
        /* @var $salesRepHelper Epicor_SalesRep_Helper_Data */

        if (!$salesRepHelper->isEnabled()) {
            return;
        }

        $isSalesRep = $this->customerSession->getCustomer()->isSalesRep();

        if ($isSalesRep) {
            $columns = $observer->getEvent()->getColumns();
            /* @var $columns Varien_Object */
            $price = $columns->getPrice();
            $price['renderer'] = 'Epicor\SalesRep\Block\Crqs\Details\Lines\Renderer\Currency';
            $columns->setPrice($price);
        }
    }

}