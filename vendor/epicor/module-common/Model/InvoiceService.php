<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Model;

use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\Product\Type;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;

class InvoiceService extends \Magento\Sales\Model\Service\InvoiceService
{
    /**
     * Creates an invoice based on the order and quantities provided
     *
     * @param Order $order
     * @param array $qtys
     * @return \Magento\Sales\Model\Order\Invoice
     * @throws \Magento\Framework\Exception\LocalizedException
     * We override this function because it breaks when we place order with 1 simple product with 2 qty and one kit product of qty 1
     * Order is palced with 1 invoiced qty but it wont support "bundle_selection_attributes"
     */
    public function prepareInvoice(Order $order, array $qtys = []): InvoiceInterface
    {
        $invoice = $this->orderConverter->toInvoice($order);
        $totalQty = 0;
        $qtys = $this->prepareItemsQtyEcc($order, $qtys);
        foreach ($order->getAllItems() as $orderItem) {
            if (!$this->canInvoiceItemECC($orderItem, $qtys)) {
                continue;
            }
            $item = $this->orderConverter->itemToInvoiceItem($orderItem);
            if (isset($qtys[$orderItem->getId()])) {
                $qty = (double) $qtys[$orderItem->getId()];
            } elseif ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } elseif (empty($qtys)) {
                $qty = $orderItem->getQtyToInvoice();
            } else {
                $qty = 0;
            }
            $totalQty += $qty;
            $this->setInvoiceItemQuantityEcc($item, $qty);
            $invoice->addItem($item);
        }
        $invoice->setTotalQty($totalQty);
        $invoice->collectTotals();
        $order->getInvoiceCollection()->addItem($invoice);
        return $invoice;
    }
    /**
     * Prepare qty to invoice for parent and child products if theirs qty is not specified in initial request.
     *
     * @param Order $order
     * @param array $qtys
     * @return array
     */

    private function prepareItemsQtyEcc(Order $order, array $qtys = [])
    {
        foreach ($order->getAllItems() as $orderItem) {
            if (empty($qtys[$orderItem->getId()])) {
                continue;
            }
            if ($orderItem->isDummy()) {
                if ($orderItem->getHasChildren()) {
                    foreach ($orderItem->getChildrenItems() as $child) {
                        if (!isset($qtys[$child->getId()])) {
                            $qtys[$child->getId()] = $child->getQtyToInvoice();
                        }
                    }
                } elseif ($orderItem->getParentItem()) {
                    $parent = $orderItem->getParentItem();
                    if (!isset($qtys[$parent->getId()])) {
                        $qtys[$parent->getId()] = $parent->getQtyToInvoice();
                    }
                }
            }
        }

        return $qtys;
    }

    /**
     * Set quantity to invoice item.
     *
     * @param InvoiceItemInterface $item
     * @param float $qty
     * @return InvoiceManagementInterface
     * @throws LocalizedException
     */
    private function setInvoiceItemQuantityEcc(InvoiceItemInterface $item, float $qty)
    {
        $qty = ($item->getOrderItem()->getIsQtyDecimal()) ? (double) $qty : (int) $qty;
        $qty = $qty > 0 ? $qty : 0;

        /**
         * Check qty availability
         */
        $qtyToInvoice = sprintf("%F", $item->getOrderItem()->getQtyToInvoice());
        $qty = sprintf("%F", $qty);
        if ($qty > $qtyToInvoice && !$item->getOrderItem()->isDummy()) {
            throw new LocalizedException(
                __('We found an invalid quantity to invoice item "%1".', $item->getName())
            );
        }

        $item->setQty($qty);

        return $this;
    }

    /**
     * overwrite this function because of M233 do this function to private
     * Check if order item can be invoiced.
     *
     * @param OrderItemInterface $item
     * @param array $qtys
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function canInvoiceItemECC(OrderItemInterface $item, array $qtys)
    {
        if(method_exists(get_parent_class($this), '_canInvoiceItem')){ //for lessthan M233
            return $this->_canInvoiceItem($item, $qtys);
        }

        if ($item->getLockedDoInvoice()) {
            return false;
        }
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($child->getQtyToInvoice() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyToInvoice() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToInvoice() > 0;
        }
    }
}