<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

class PostDispatchActions extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Does any custom saving of a customer after save action in admin
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->registry->registry('check_licensing_config')) {
            $helper = $this->commonHelper->create();
            /* @var $helper \Epicor\Common\Helper\Data */

            $helper->processLicenseChange();
        }
    }

}