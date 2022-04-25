<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Helper
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model;


use Epicor\Punchout\Helper\Data;

/**
 * TransferCart Item Class.
 */
class TransferCartItem extends AbstractPunchout
{

    /**
     * Attribute key word
     *
     * @var string
     */
    protected $_attributes = '_attributes';

    /**
     * Connection attribute mapping
     *
     * @var array
     */
    protected $mapping = [];


    /**
     * Product needed attributes
     *
     * @var array
     */
    protected $neededColumns = [];


    /**
     * Product Attributes
     *
     * @var array
     */
    protected $productAttribute = [];


    /**
     * Quote Item Tags
     *
     * @var array
     */
    protected $itemTags = [];


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $productResourceModelFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Helper.
     *
     * @var helper
     */
    protected $helper;


    /**
     * Construction function.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceModelFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Data $helper Helper class.
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceModelFactory,
        \Magento\Customer\Model\Session $customerSession,
        Data $helper
    )
    {
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->productResourceModelFactory = $productResourceModelFactory;
        $this->customerSession = $customerSession;
        $this->helper = $helper;

    }//end __construct()

    /**
     * Get Quote Session.
     *
     * @return mixed
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;

    }


    /**
     * Updates product visibility direct to it's index
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product
     */

    public function catalogResourceModelProductFactory()
    {
        return $this->productResourceModelFactory->create();
    }


    /**
     * Prepare Item with Mapping.
     *
     * @param array $mappings
     * @return array
     */
    public function processMapping($mappings)
    {
        $finalMappings = [];
        $this->neededColumns[] = 'ecc_uom';
        $this->neededColumns[] = 'sku';
        foreach ($mappings as $mapping) {
            if ($mapping['include']) {
                $header = explode('_', $mapping['header']);
                if (!isset($header[1])) {
                    $this->itemTags[] = 'ItemDetail';
                    $finalMappings['ItemDetail'][] = [
                        'tagname' => $header[0],
                        'fieldname' => $mapping['map'],
                        'type' => $mapping['type'],

                    ];
                } else {
                    $this->itemTags[] = $header[0];
                    $finalMappings[$header[0]][] = [
                        'tagname' => $header[1],
                        'fieldname' => $mapping['map'],
                        'type' => $mapping['type'],
                    ];
                }
                $this->neededColumns[] = $mapping['map'];
            }
        }
        $this->mapping = $finalMappings;
        return $finalMappings;
    }

    /**
     * Prepare PunchOut Order Items tags.
     *
     * @return array
     */
    public function getQuoteItems()
    {
        $quote = $this->getCheckoutSession()->getQuote();
        $this->preProccessAttribute($quote);
        $items = [];
        $i = 1;
        foreach ($quote->getAllItems() as $item) {
            if (!$item->isDeleted() && $item->getParentItemId() == null && ($item->getParentId() == null || $this->getPromotions())) {
                $itemDetailArray = [];
                if ($item->getProductType() == 'configurable') {
                    $productId = $this->getChildProductid($item);
                } else {
                    $productId = $item->getProductId();
                }
                $storeId = $item->getStoreId();
                $productinfo = $this->catalogResourceModelProductFactory()
                    ->getAttributeRawValue($productId, $this->neededColumns, $storeId);
                $productinfo = $this->preProccessproductInfo($productinfo, $item);
                $itemIdtags = [
                    $this->_attributes => [
                        'quantity' => $item->getQty(),
                        'lineNumber' => $i
                    ]
                ];
                $uomArr = $this->helper->getCommHelper()->splitProductCode($item->getSku());
                $productSku = $uomArr[0];
                $productinfo['sku'] = $productSku;
                $itemIDArray = [
                    'SupplierPartID' => $productSku,
                    'SupplierPartAuxiliaryID' => $quote->getId()
                ];
                $itemIdtags['ItemID'] = $itemIDArray;
                $currencyCode = $this->storeManager->getStore()->getBaseCurrencyCode();
                $itemDetailArray['UnitPrice'] = [
                    'Money' => [
                        $this->_attributes => [
                            'currency' => $currencyCode
                        ],
                        $item->getBasePrice()
                    ]
                ];
                $itemDetailArray['UnitOfMeasure'] = $productinfo['ecc_uom'];
                $locationcode = $item->getEccLocationCode() ?: '';
                $ewacode = $this->getEwaCode($item);

                $itemDetailArray['Extrinsic'][] = [
                    $this->_attributes => [
                        'name' => 'locationCode'
                    ],
                    $locationcode
                ];
                $itemDetailArray['Extrinsic'][] = [
                    $this->_attributes => [
                        'name' => 'ewaCode'
                    ],
                    $ewacode
                ];
                $itemIdtags['ItemDetail'] = $itemDetailArray;
                $itemIdtags = $this->prepareItemTags($itemIdtags, $itemIDArray, $productinfo, $itemDetailArray);
                $itemIdtags['tax'] = '';
                if ($item->getTaxAmount()) {
                    $itemIdtags['tax'] = [
                        'Money' => [
                            $this->_attributes => [
                                'currency' => $currencyCode
                            ],
                            $item->getTaxAmount()
                        ]
                    ];
                }
                $items[] = $itemIdtags;
                $i++;
            }
        }

        return $items;
    }

    /**
     * Get Child Product Id.
     *
     * @param $item
     * @return int
     */
    private function getChildProductid($item)
    {
        $connection = $item->getResource()->getConnection();
        return $connection->fetchOne(
            $connection->select()->from(
                $connection->getTableName('quote_item'),
                ['product_id']
            )->where('parent_item_id = ?', $item->getId())
        );
    }

    /**
     * Prepare Itemtags.
     *
     * @param $itemIdtags
     * @return array
     */
    private function prepareItemTags($itemIdtags, $itemIDArray, $productinfo, $itemDetailArray)
    {
        foreach ($this->itemTags as $itemTag) {
            if ($itemTag == 'ItemID') {
                $itemIdtags[$itemTag] = array_merge(
                    $itemIDArray,
                    $this->getItemID($productinfo, $itemTag, $itemIDArray)
                );
            } else if ($itemTag == 'ItemDetail') {
                $itemIdtags[$itemTag] = array_merge(
                    $itemDetailArray,
                    $this->getItemID($productinfo, $itemTag, $itemDetailArray)
                );
            } else {
                $itemIdtags[$itemTag] = $this->getItemID($productinfo, $itemTag);
            }
        }
        return $itemIdtags;
    }

    /**
     * Item EwaCode.
     *
     * @param $item
     * @return string
     */
    private function getEwaCode($item)
    {
        $ewacode = '';
        $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        if (isset($productOptions['options']) && is_array($productOptions['options'])) {
            foreach ($productOptions['options'] as $option) {
                if ($option['option_type'] == 'ewa_code') {
                    $ewacode = $option['value'];
                }
            }
        }
        return $ewacode;
    }

    /**
     * Proccess Item Tags.
     *
     * @param $item
     * @param $productinfo
     * @param $itemtag
     * @return array
     */
    private function getItemID($productinfo, $itemtag, $defaultData = [])
    {
        $itemId = $defaultData;

        $itemIdTags = $this->mapping[$itemtag];
        foreach ($itemIdTags as $itemIdTag) {
            $attributeValue = '';
            if (isset($productinfo[$itemIdTag['fieldname']])) {
                $attributeValue = $productinfo[$itemIdTag['fieldname']];
            }
            if ($itemIdTag['type'] == 'regular') {
                if (!isset($itemId[$itemIdTag['tagname']])) {
                    $itemId[$itemIdTag['tagname']][] = $attributeValue;
                }
            } elseif ($itemIdTag['type'] == 'classification') {
                $itemId['Classification'][] = [
                    $this->_attributes => [
                        'domain' => $itemIdTag['tagname']
                    ],
                    $attributeValue

                ];
            } elseif ($itemIdTag['type'] == 'extrinsic') {
                $itemId['Extrinsic'][] = [
                    $this->_attributes => [
                        'name' => $itemIdTag['tagname']
                    ],
                    $attributeValue

                ];
            }
        }
        return $itemId;
    }

    /**
     * Proccess Product Information.
     *
     * @param $productinfo
     * @param $item
     * @return array
     */
    private function preProccessproductInfo($productinfo, $item)
    {
        foreach ($productinfo as $code => $value) {
            if (isset($this->productAttribute[$code]) && $this->productAttribute[$code] == 'select') {
                $attr = $item->getProduct()->getResource()->getAttribute($code);
                $productinfo[$code] = $attr->getSource()->getOptionText($productinfo[$code]);
            }
            if (isset($this->productAttribute[$code]) && $this->productAttribute[$code] == 'multiselect') {
                $attr = $item->getProduct()->getResource()->getAttribute($code);
                $valueIds = explode(',', $productinfo[$code]);
                $valueText = [];
                foreach ($valueIds as $valueId) {
                    $valueText[] = $attr->getSource()->getOptionText($valueId);
                }
                $productinfo[$code] = implode(',', $valueText);
            }
        }
        return $productinfo;
    }

    /**
     * Proccess Attriute for Dropdown vales.
     *
     * @param $quote
     * @return array
     */
    private function preProccessAttribute($quote)
    {
        $connection = $quote->getResource()->getConnection();
        $attributes = $connection->fetchAll(
            $connection->select()->from(
                $connection->getTableName('eav_attribute'),
                ['attribute_code', 'frontend_input']
            )->where(
                'frontend_input in ("select","multiselect")'
            )
        );
        foreach ($attributes as $attribute) {
            $this->productAttribute[$attribute['attribute_code']] = $attribute['frontend_input'];
        }
    }
}//end class