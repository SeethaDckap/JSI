<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

use Magento\Framework\Event\ObserverInterface;

class ChangeTemplateimgsync implements ObserverInterface
{
    /**
     * @param mixed $observer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getBlock()->setTemplate('Epicor_Comm::catalog/product/helper/gallery.phtml');
        $observer->getBlock()->addChild('erpimages', \Epicor\Comm\Block\Adminhtml\Form\Element\ListErpImages::class);

    }
}
