<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Search\Observer;

class SetCreatedBy extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $attribute =  $observer->getEvent()->getAttribute();
        if ($attribute->getAttributeId()) {
            $attributeModel = $this->eavEntityAttributeFactory->create()->load($attribute->getAttributeId());
            if (!$attributeModel->getEccCreatedBy()) {
                $attributeModel->setEccCreatedBy('N');
                $attributeModel->save();
            }
        }
    }

}
