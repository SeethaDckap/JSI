<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\CatalogInventory;

use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Catalog\Model\ProductFactory;

/**
 * Description of StockStateProviderPlugin
 */
class StockStateProviderPlugin
{

    /**
     * @var CatalogResourceModelProductFactory
     */
    private $catalogResourceModelProductFactoryExist = null;
    /**
     * @var ObjectFactory
     */
    protected $objectFactory;
    /**
     * @var FormatInterface
     */
    protected $localeFormat;
    /*
     * @ var \Epicor\Comm\Helper\Locations
     */
    protected $locHelper;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $catalogResourceModelProductFactory;

    public function __construct(
        FormatInterface $localeFormat,
        ObjectFactory $objectFactory,
        ProductFactory $productFactory,
        \Epicor\Comm\Helper\Locations $locHelper,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $catalogResourceModelProductFactory
    )
    {
        $this->objectFactory = $objectFactory;
        $this->localeFormat = $localeFormat;
        $this->locHelper = $locHelper;
        $this->productFactory = $productFactory;
        $this->catalogResourceModelProductFactory = $catalogResourceModelProductFactory;
    }

    /**
     * @param string|float|int|null $qty
     * @return float|null
     */
    protected function getNumber($qty)
    {
        if (!is_numeric($qty)) {
            $qty = $this->localeFormat->getNumber($qty);
            return $qty;
        }
        return $qty;
    }

    /**
     * Resource Factory.
     * @return \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    public function catalogResourceModelProductFactory()
    {
        if (!$this->catalogResourceModelProductFactoryExist) {
            $this->catalogResourceModelProductFactoryExist = $this->catalogResourceModelProductFactory->create();
        }
        return $this->catalogResourceModelProductFactoryExist;
    }

    /**
     * @param \Magento\CatalogInventory\Model\StockStateProvider $subject
     * @param \Closure
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @param int|float $summaryQty
     * @param int|float $origQty
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

    public function aroundCheckQuoteItemQty(
        \Magento\CatalogInventory\Model\StockStateProvider $subject,
        \Closure $proceed,
        \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem,
        $qty,
        $summaryQty,
        $origQty = 0
    )
    {

        //$result = $proceed($stockItem, $qty,$summaryQty, $origQty);
        $result = $this->objectFactory->create();
        $result->setHasError(false);

        $qty = $this->getNumber($qty);

        /**
         * Check quantity type
         */
        $result->setItemIsQtyDecimal(1);
        $eccDecimalPlaces = $this->catalogResourceModelProductFactory()->getAttributeRawValue(
            $stockItem->getProductId(), 'ecc_decimal_places', $stockItem->getStoreId()
        );
        $decimalPlaces = $this->locHelper->getDecimalPlaces($eccDecimalPlaces);
        if (!$stockItem->getIsQtyDecimal()) {
            $result->setHasQtyOptionUpdate(true);
            $qty = $this->locHelper->qtyRounding($qty, $decimalPlaces);
            /**
             * Adding stock data to quote item
             */
            $result->setItemQty($qty);
            $qty = $this->getNumber($qty);
            $origQty = $this->locHelper->qtyRounding($origQty, $decimalPlaces);
            $result->setOrigQty($origQty);
        }
        /* @var $locHelper Epicor_Comm_Helper_Locations */
        $locEnabled = false;

        if ($stockItem->getMinSaleQty() && $qty < $stockItem->getMinSaleQty()) {
            $result->setHasError(true)
                ->setMessage(__('The fewest you may purchase is %1.', $stockItem->getMinSaleQty() * 1))
                ->setErrorCode('qty_min')
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($stockItem->getMaxSaleQty() && $qty > $stockItem->getMaxSaleQty()) {
            $result->setHasError(true)
                ->setMessage(__('The most you may purchase is %1.', $stockItem->getMaxSaleQty() * 1))
                ->setErrorCode('qty_max')
                ->setQuoteMessage(__('Please correct the quantity for some products.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        $result->addData($subject->checkQtyIncrements($stockItem, $qty)->getData());
        if ($result->getHasError()) {
            return $result;
        }

        if (!$stockItem->getManageStock()) {
            return $result;
        }

        if (!$stockItem->getIsInStock() && !$stockItem->getBackorders()) {
            $result->setHasError(true)
                ->setMessage(__($stockItem->getProductName().' product is out of stock.'))
                ->setQuoteMessage(__('Some of the products are out of stock.'))
                ->setQuoteMessageIndex('stock');
            $result->setItemUseOldQty(true);
            return $result;
        }

        $validateQty = $stockItem->getIsEccDiscontinued() ? false : $locEnabled;
        if (!$validateQty && (!$subject->checkQty($stockItem, $summaryQty) || !$subject->checkQty($stockItem, $qty))) {
            $message = __('We don\'t have as many "%1" as you requested.', $stockItem->getProductName());
            $result->setHasError(true)->setMessage($message)->setQuoteMessage($message)->setQuoteMessageIndex('qty');
            return $result;
        } else {
            if ($stockItem->getQty() - $summaryQty < 0) {
                if ($stockItem->getProductName()) {
                    if ($stockItem->getIsChildItem()) {
                        $backOrderQty = $stockItem->getQty() > 0 ? ($summaryQty - $stockItem->getQty()) * 1 : $qty * 1;
                        if ($backOrderQty > $qty) {
                            $backOrderQty = $qty;
                        }

                        $result->setItemBackorders($backOrderQty);
                    } else {
                        $orderedItems = $stockItem->getOrderedItems();

                        // Available item qty in stock excluding item qty in other quotes
                        $qtyAvailable = ($stockItem->getQty() - ($summaryQty - $qty)) * 1;
                        if ($qtyAvailable > 0) {
                            $backOrderQty = $qty * 1 - $qtyAvailable;
                        } else {
                            $backOrderQty = $qty * 1;
                        }
                        $backOrderQty = $this->locHelper->qtyRounding($backOrderQty, $decimalPlaces);

                        if ($backOrderQty > 0) {
                            $result->setItemBackorders($backOrderQty);
                        }
                        $stockItem->setOrderedItems($orderedItems + $qty);
                    }

                    if ($stockItem->getBackorders() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$stockItem->getIsChildItem()) {
                            $result->setMessage(
                                __(
                                    'We don\'t have as many "%1" as you requested, but we\'ll back order the remaining %2.',
                                    $stockItem->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        } else {
                            $result->setMessage(
                                __(
                                    'We don\'t have "%1" in the requested quantity, so we\'ll back order the remaining %2.',
                                    $stockItem->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        }
                    } elseif ($stockItem->getShowDefaultNotificationMessage()) {
                        $result->setMessage(
                            __('We don\'t have as many "%1" as you requested.', $stockItem->getProductName())
                        );
                    }
                }
            } else {
                if (!$stockItem->getIsChildItem()) {
                    $stockItem->setOrderedItems($qty + $stockItem->getOrderedItems());
                }
            }
        }
        return $result;
    }

}
