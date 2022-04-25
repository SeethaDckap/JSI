<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class AddCreatedByToAttributeField extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor\Comm\Model\Observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $form = $observer->getEvent()->getForm();
        $fieldset = $form->getElement('base_fieldset');
        $fieldset->addField('ecc_created_by', 'text', array(
            'name' => 'ecc_created_by',
            'label' => __('Created By'),
            'title' => __('Created By'),
            'disabled' => true,
        ));
    }

}