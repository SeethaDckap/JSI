<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

class ProductRelatedDocSaveBefore extends AbstractObserver
{
    /**
     * -checks to see if related document array is present or not from product admin page
     * if not then add empty array
     *
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $productParams = $this->request->getParam('product');
        $isEdit = !empty($this->request->getParam('back')) ? $this->request->getParam('back') : '';
        if (isset($isEdit) && $isEdit == 'edit') {
            (isset($productParams['ecc_related_documents']) && count($productParams['ecc_related_documents']) > 0) ?
                $product->setData('ecc_related_documents', $productParams['ecc_related_documents']) :
                $product->setData('ecc_related_documents', null);
        }
    }
}
