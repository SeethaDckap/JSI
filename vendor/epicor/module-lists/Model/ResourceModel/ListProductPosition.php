<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Model\ResourceModel;

use Epicor\Comm\Model\Product as EccProduct;
use Epicor\Database\Model\ResourceModel\Lists\Product\Collection as ListProductCollection;
use Epicor\Database\Model\ResourceModel\Lists\Product\CollectionFactory as ProductListCollectionFactory;
use Epicor\Lists\Helper\Session as ListHelperSession;
use Epicor\Lists\Model\ListModel as EccListModel;
use Epicor\Lists\Model\ResourceModel\ListModel\Collection as ListCollection;
use Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory as ListModelCollectionFactory;
use Magento\Framework\App\ResourceConnection as ResourceConnection;

class ListProductPosition
{
    private $assignedProducts;
    /**
     * @var ProductListCollectionFactory
     */
    private $listProductFactory;
    /**
     * @var EccProduct
     */
    private $eccProduct;
    private $productLinkData = [];
    /**
     * @var ListModelCollectionFactory
     */
    private $listModelCollectionFactory;
    /**
     * @var ListHelperSession
     */
    private $listHelperSession;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * ListProductPosition constructor.
     * @param ProductListCollectionFactory $listProductFactory
     * @param EccProduct $eccProduct
     * @param ListModelCollectionFactory $listModelCollectionFactory
     * @param ListHelperSession $listHelperSession
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ProductListCollectionFactory $listProductFactory,
        EccProduct $eccProduct,
        ListModelCollectionFactory $listModelCollectionFactory,
        ListHelperSession $listHelperSession,
        ResourceConnection $resourceConnection
    ){
        $this->listProductFactory = $listProductFactory;
        $this->eccProduct = $eccProduct;
        $this->listModelCollectionFactory = $listModelCollectionFactory;
        $this->listHelperSession = $listHelperSession;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param $list
     * @param $jsonAssignedProducts
     * @param $productLinkData
     * @return bool
     */
    public function saveProductPositionJsonData($list, $jsonAssignedProducts, $productLinkData)
    {
        if (!$list instanceof EccListModel) {
            return false;
        }
        $positionIsSet = false;
        $this->productLinkData = $productLinkData;
        $this->assignedProducts = json_decode($jsonAssignedProducts, true);
        $selectedSkuValues = $this->getSelectedProductsSkuValues();
        $selectedListProductCollection = $this->getSelectedListProductCollection($selectedSkuValues, $list);

        foreach ($selectedListProductCollection as $productListItem) {
            $productEntityId = $this->eccProduct->getIdBySku($productListItem->getSku());
            $assignedPosition = $this->assignedProducts[$productEntityId];
            $productListItem->setData('list_position', $assignedPosition);
            $productListItem->save();
            if(is_numeric($assignedPosition)){
                $positionIsSet = true;
            }
        }
        $positionSetValue = $positionIsSet ? 1 : 0;
        $this->setPositionDefaultOrderConfig($list->getId(), $positionSetValue);
    }

    /**
     * @param $list
     * @param $cartItems
     * @return bool
     */
    public function savePositionOrder($list, $cartItems)
    {
        if (!$list instanceof EccListModel) {
            return false;
        }
        $positionIsSet = false;
        $cartItemSkuValues = [];
        $cartItemSkuWithLocationValues =[];
        $position = 1;
        foreach ($cartItems as $item) {
            $locationCode = (!empty($item->getEccLocationCode())) ? $item->getEccLocationCode() : '';
            $cartItemSkuWithLocationValues[$position] = $item->getSku().'_'. $locationCode;
            $cartItemSkuValues[$position] = $item->getSku();
            $position++;
        }
        $listProductCollection = $this->getSelectedListProductCollection($cartItemSkuValues, $list);
        foreach ($listProductCollection as $listProduct) {
            $listPosition = array_search($listProduct->getSku().'_'. $listProduct->getLocationCode(), $cartItemSkuWithLocationValues, false);
            $listProduct->setData('list_position', $listPosition);
            $listProduct->save();
            $positionIsSet = true;
        }
        $positionSetValue = $positionIsSet ? 1 : 0;
        $this->setPositionDefaultOrderConfig($list->getId(), $positionSetValue);
    }

    /**
     * @return mixed
     */
    public function getQuickOrderPadListId()
    {
        return $this->listHelperSession->getValue('ecc_quickorderpad_list');
    }

    /**
     * @param $listId
     * @param $positionSetValue
     */
    public function setPositionDefaultOrderConfig($listId, $positionSetValue)
    {
        $configValue = (int) $positionSetValue;
        if ($listId && ($configValue === 1 || $configValue === 0)) {
            $table = $this->resourceConnection->getTableName('ecc_list');
            $sql = "update $table set is_position_order_set=$configValue where id=$listId";
            $this->resourceConnection->getConnection()->query($sql);
        }
    }

    /**
     * @param $listId
     * @return bool
     */
    public function isListPositionOrderSet($listId): bool
    {
        if (!$this->listModelCollectionFactory) {
            return false;
        }
        /** @var  $listCollection ListCollection */
        $listCollection = $this->listModelCollectionFactory->create();
        $listCollection->addFieldToFilter('id', $listId);
        $list = $listCollection->getFirstItem();

        $positionOrderSet = (int) $list->getData('is_position_order_set');
        return $positionOrderSet === 1;
    }

    /**
     * @return array|bool
     */
    private function getSelectedProductsSkuValues()
    {
        if (!is_array($this->productLinkData)) {
            return false;
        }

        $selectedProductSkuValues = [];
        foreach ($this->productLinkData as $index => $sku) {
            if (is_numeric($index) && $sku !== 'on') {
                $selectedProductSkuValues[] = $sku;
            }
        }

        return $selectedProductSkuValues;
    }

    /**
     * @param $selectedSkuValues
     * @param $list
     * @return ListProductCollection
     */
    private function getSelectedListProductCollection($selectedSkuValues, $list)
    {
        if ($list instanceof EccListModel) {
            /** @var ListProductCollection $selectedListProductCollection */
            $selectedListProductCollection = $this->listProductFactory->create();
            $selectedListProductCollection->addFieldToFilter('list_id', $list->getId());
            $selectedListProductCollection->addFieldToFilter('sku', ['in' => $selectedSkuValues]);

            return $selectedListProductCollection;
        }
    }


    /**
     * Get Quote Id By Item Id.
     *
     * @param string $quoteItemId Quote Item Id.
     *
     * @return int|string
     */
    public function getQuoteIdByItemId($quoteItemId)
    {
        $connection    = $this->resourceConnection->getConnection();
        $select        = $connection->select()->from(
            'quote_item',
            'quote_id'
        )->where("item_id ='".$quoteItemId."'");
        $quoteIdResult = $connection->fetchOne($select);
        if (!$quoteIdResult) {
            $quoteIdResult = 0;
        }

        return $quoteIdResult;

    }//end getQuoteIdByItemId()


    /**
     * Get Quantity for product based upon location.
     *
     * @param $item
     *
     * @return int
     */
    public function getQuantityDetails($item)
    {
        $quoteId    = !empty($item->getQuoteId()) ? $item->getQuoteId() : $this->getQuoteIdByItemId($item->getQuoteItemId());
        $bind       = [
            'productId' => $item->getProductId(),
            'quoteId'   => $quoteId,
        ];
        $location   = (!empty($item->getEccLocationCode())) ? $item->getEccLocationCode() : 'NULL';
        $connection = $this->resourceConnection->getConnection();
        $select     = $connection->select()->from(
            'quote_item',
            'sum(qty)'
        )->where(
            $connection->quoteIdentifier('product_id').' = :productId'
        )->where(
            $connection->quoteIdentifier('quote_Id').' = :quoteId'
        );
        if ($location === 'NULL') {
            $select->where($connection->quoteIdentifier('ecc_location_code').' is NULL');
        } else {
            $select->where($connection->quoteIdentifier('ecc_location_code')."='$location'");
        }

        $qty = $connection->fetchOne($select, $bind);
        if (!$qty) {
            $qty = 1;
        }

        return $qty;

    }//end getQuantityDetails()


    /**
     * Check Product's location data.
     *
     * @param $item
     *
     * @return int
     */
    public function getLocationDetails($item)
    {
        $quoteId    = !empty($item->getQuoteId()) ? $item->getQuoteId() : $this->getQuoteIdByItemId($item->getQuoteItemId());
        $bind       = [
            'productId' => $item->getProductId(),
            'quoteId'   => $quoteId,
        ];
        $connection = $this->resourceConnection->getConnection();
        $select     = $connection->select()->from(
            'quote_item',
            'ecc_location_code'
        )->where(
            $connection->quoteIdentifier('product_id').' = :productId'
        )->where($connection->quoteIdentifier('quote_Id').' = :quoteId');
        $location   = $connection->fetchCol($select, $bind);
        if (!empty($item->getEccLocationCode())) {
            $locationsDup = array_count_values($location);
            $locationDup  = $locationsDup[$item->getEccLocationCode()];
            if ($locationDup > 1) {
                return 1;
            }
        } else if (count(array_unique($location)) < count($location)) {
            return 1;
        }

        return 0;

    }//end getLocationDetails()


}