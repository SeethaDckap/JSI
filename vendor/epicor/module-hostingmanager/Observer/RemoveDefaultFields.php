<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Observer;

class RemoveDefaultFields extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Remove Default Fields to Website / Store Group Edit Pages
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * 
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        $storeData = $this->registry->registry('store_data');
        $form = $block->getForm();
        if ($storeData instanceof \Magento\Store\Model\Group && $storeData->getIsDefault()) {
            $fieldset = $form->getElement('group_fieldset');
            /* @var $fieldset Varien_Data_Form_Element_Fieldset */
            $fieldset->removeField('group_default_store_id');
        } elseif ($storeData instanceof \Magento\Store\Model\Website) {
            $fieldset = $form->getElement('website_fieldset');
            /* @var $fieldset Varien_Data_Form_Element_Fieldset */
            if ($storeData->getIsDefault()) {
                $fieldset->removeField('website_default_group_id');
            } else {
                $fieldset->removeField('is_default');
            }
        }


        $block->setForm($form);
    }

}