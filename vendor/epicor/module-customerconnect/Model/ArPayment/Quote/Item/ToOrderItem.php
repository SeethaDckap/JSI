<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Model\ArPayment\Quote\Item;

use Magento\Framework\DataObject\Copy;
use Epicor\Customerconnect\Model\ArPayment\Quote\Item;
use Epicor\Customerconnect\Model\ArPayment\Quote\Address\Item as AddressItem;
use Epicor\Customerconnect\Api\Data\OrderItemInterfaceFactory as OrderItemFactory;
use Epicor\Customerconnect\Api\Data\OrderItemInterface;

/**
 * Class ToOrderItem
 */
class ToOrderItem
{
    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var OrderItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param OrderItemFactory $orderItemFactory
     * @param Copy $objectCopyService
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        OrderItemFactory $orderItemFactory,
        Copy $objectCopyService,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->objectCopyService = $objectCopyService;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param Item|AddressItem $item
     * @param array $data
     * @return OrderItemInterface
     */
    public function convert($item, $data = [])
    {
        $options = $item->getProductOrderOptions();
        
        $orderItemData = $this->objectCopyService->getDataFromFieldset(
            'quote_convert_item',
            'to_order_item',
            $item
        );
        if (!$item->getNoDiscount()) {
            $data = array_merge(
                $data,
                $this->objectCopyService->getDataFromFieldset(
                    'quote_convert_item',
                    'to_order_item_discount',
                    $item
                )
            );
        }

        $orderItem = $this->orderItemFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $orderItem,
            array_merge($orderItemData, $data),
            \picor\Customerconnect\Api\Data\OrderItemInterface::class
        );
        $orderItem->setProductOptions($options);
        if ($item->getParentItem()) {
            $orderItem->setQtyOrdered(
                $orderItemData[OrderItemInterface::QTY_ORDERED] * $item->getParentItem()->getQty()
            );
        }
        return $orderItem;
    }
}
