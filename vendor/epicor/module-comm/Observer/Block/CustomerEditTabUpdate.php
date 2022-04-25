<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer\Block;

class CustomerEditTabUpdate extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Updates a file model to send an FREQ if the file is not found locally
     * 
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Epicor_Comm_Model_Observer_File
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();

        /* @var $block Mage_Core_Block_Abstract */

        if ($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tabs) {
            /* @var $block Mage_Adminhtml_Block_Customer_Edit_Tabs */
            $block->setTabData('addresses', 'content', null);
            $block->setTabData('addresses', 'class', 'ajax');
            $block->setTabData('addresses', 'url', 'adminhtml/epicorcomm_customer/addresses');
        } else if ($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses) {
            /* @var $block Mage_Adminhtml_Block_Customer_Edit_Tab_Addresses */

            if (!$this->request->getParam('isAjax')) {
                $block->assign('addressCollection', array());
            } else {
                $block->setTemplate('epicor_comm/customer/tab/addresses.phtml');
            }
        }

        return $this;
    }

}