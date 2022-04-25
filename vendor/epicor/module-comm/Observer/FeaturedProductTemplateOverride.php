<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class FeaturedProductTemplateOverride extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        /* @var $block Epicor_FlexiTheme_Block_Frontend_Callout */

        $block->setTemplate('epicor_comm/flexitheme/page/template/callout/product/side.phtml');
    }

}