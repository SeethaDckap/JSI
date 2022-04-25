<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class ProcessProductSave extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Processes product save
     * 
     * - checks to see if erp images needs resyncing
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $product = $observer->getEvent()->getProduct();
        /* @var $product Epicor_Comm_Model_Product */

        if ($product->getResyncImagesAfterSave()) {
            $helper = $this->commProductImageSyncHelper->create();
            /* @var $helper Epicor_Comm_Helper_Product */
            $helper->processErpImages($product->getId());
        }
    }

}