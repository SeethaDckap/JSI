<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Category;

class AddSyncUrlsBlock extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Adds JavaScript Sync Urls Block to Ajax Category Response
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getResponse();
        /* @var $response Varien_Data */

        $urlsBlock = $this->layout->createBlock('epicor_comm/adminhtml_catalog_category_edit_sync');
        /* @var $urlsBlock Epicor_Comm_Adminhtml_Catalog_Category_Edit_Sync */
        $urlsBlock->setTemplate('epicor_comm/catalog/product/edit/sync.phtml');

        $content = $response->getContent() . $urlsBlock->toHtml();

        $response->setContent($content);
    }

}