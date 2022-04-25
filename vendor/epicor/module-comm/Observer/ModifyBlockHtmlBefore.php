<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

class ModifyBlockHtmlBefore extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $commHelperExist = null;

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */


    public function commHelper()
    {
        if (!$this->commHelperExist) {
            $this->commHelperExist = $this->commHelper->create();;
        }
        return $this->commHelperExist;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof \Epicor\Customerconnect\Block\Customer\Dashboard\Orders\Grid
            || $block instanceof \Epicor\Customerconnect\Block\Customer\Orders\Listing\Grid
        ) {
    //        $helper = $this->commHelper();
//            if ($helper->isFunctionalityDisabledForCustomer('cart')) {
////                $block->removeColumn('reorder');
////            }
        }
//        if ($block instanceof \Magento\Bundle\Block\Catalog\Product\Price && !($block instanceof \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option)) {
//            $handlesArray = $block->getLayout()->getUpdate()->getHandles();
//            $requiredHandles = array('catalogsearch_result_index', 'catalog_category_view', 'catalogsearch_advanced_result');
//            foreach ($requiredHandles as $handle) {
//                if (in_array($handle, $handlesArray)) {                      // only changes the bundle price for required handle
//                    $block->setTemplate('epicor_comm/catalog/product/price.phtml');
//                }
//            }
//        }

        if ($block instanceof \Magento\Sales\Block\Adminhtml\Order\Totals ||
            $block instanceof \Magento\Sales\Block\Adminhtml\Order\Invoice\Totals ||
            $block instanceof \Magento\Sales\Block\Adminhtml\Order\Creditmemo\Totals) {

            // this bit is required because in Enterprise edition, other blocks are instances of sales_order_totals. This code filters them out
            //$valid_values = array('adminhtml/sales_order_totals', 'adminhtml/sales_order_invoice_totals', 'adminhtml/sales_order_creditmemo_totals');
            if ((!$block instanceof \Magento\Sales\Block\Adminhtml\Order\Totals\Item)) {
                $block->setTemplate('Epicor_Comm::epicor_comm/sales/order/totals.phtml');
            }
        }
    }

}