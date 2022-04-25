<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class AddCreatedByToAttributeSetField extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (!isset($block)) {
            return $this;
        }
        // this is needed as created_by is not picked up automatically, so otherwise returns blank  
        $attSetData = $this->eavEntityAttributeSetFactory->create()->load($this->request->getParam('id'));
        if ($attSetData->getEccCreatedBy()) {
            if ($block instanceof \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main\Formset ) {
                $form = $block->getForm();
                $fieldset = $form->getElement('set_name');
                $fieldset->addField('ecc_created_by', 'text', array(
                    'name' => 'ecc_created_by',
                    'label' => __('Created By'),
                    'title' => __('Created By'),
                    'disabled' => true,
                    'value' => $attSetData->getEccCreatedBy(),
                ));
            }
        }
    }

}