<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class PrepareProductSave extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Processes product prepare save
     * 
     * - checks to see if erp images have changed
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $product = $observer->getEvent()->getProduct();
        /* @var $product Epicor_Comm_Model_Product */

        $request = $observer->getEvent()->getRequest();
        /* @var $product Epicor_Comm_Model_Product */

        $params = $request->getParams();
        if (isset($params['delete_product'])) {
            if ($params['delete_product']['ecc_erp_images'] !== '') {
                $deleteImages = explode(',', $params['delete_product']['ecc_erp_images']);
                $images = $product->getEccErpImages();
                foreach ($images as $x => $image) {
                    if (in_array($x, $deleteImages)) {
                        unset($images[$x]);
                    }
                }

                $product->setEccPreviousErpImages($product->getEccErpImages());
                $product->setEccErpImages($images);
                $product->setResyncImagesAfterSave(1);
                $product->setEccErpImagesProcessed(0);
            }
        }
    }

}