<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\ArPayment\Quote;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Sales Quote Item Model
 *
 * @api
 * @method string getCreatedAt()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setUpdatedAt(string $value)
 * @method int getStoreId()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setStoreId(int $value)
 * @method int getParentItemId()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setParentItemId(int $value)
 * @method int getIsVirtual()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setIsVirtual(int $value)
 * @method string getDescription()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setDescription(string $value)
 * @method string getAdditionalData()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setAdditionalData(string $value)
 * @method int getFreeShipping()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setFreeShipping(int $value)
 * @method int getIsQtyDecimal()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setIsQtyDecimal(int $value)
 * @method int getNoDiscount()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setNoDiscount(int $value)
 * @method float getWeight()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setWeight(float $value)
 * @method float getBasePrice()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBasePrice(float $value)
 * @method float getCustomPrice()
 * @method float getTaxPercent()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setTaxPercent(float $value)
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setTaxAmount(float $value)
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBaseTaxAmount(float $value)
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setRowTotal(float $value)
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBaseRowTotal(float $value)
 * @method float getRowTotalWithDiscount()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setRowTotalWithDiscount(float $value)
 * @method float getRowWeight()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setRowWeight(float $value)
 * @method float getBaseTaxBeforeDiscount()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBaseTaxBeforeDiscount(float $value)
 * @method float getTaxBeforeDiscount()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setTaxBeforeDiscount(float $value)
 * @method float getOriginalCustomPrice()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setOriginalCustomPrice(float $value)
 * @method string getRedirectUrl()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setRedirectUrl(string $value)
 * @method float getBaseCost()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBaseCost(float $value)
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setPriceInclTax(float $value)
 * @method float getBasePriceInclTax()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBasePriceInclTax(float $value)
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setRowTotalInclTax(float $value)
 * @method float getBaseRowTotalInclTax()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBaseRowTotalInclTax(float $value)
 * @method int getGiftMessageId()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setGiftMessageId(int $value)
 * @method string getWeeeTaxApplied()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setWeeeTaxApplied(string $value)
 * @method float getWeeeTaxAppliedAmount()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setWeeeTaxAppliedAmount(float $value)
 * @method float getWeeeTaxAppliedRowAmount()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setWeeeTaxAppliedRowAmount(float $value)
 * @method float getBaseWeeeTaxAppliedAmount()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBaseWeeeTaxAppliedAmount(float $value)
 * @method float getBaseWeeeTaxAppliedRowAmnt()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBaseWeeeTaxAppliedRowAmnt(float $value)
 * @method float getWeeeTaxDisposition()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setWeeeTaxDisposition(float $value)
 * @method float getWeeeTaxRowDisposition()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setWeeeTaxRowDisposition(float $value)
 * @method float getBaseWeeeTaxDisposition()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBaseWeeeTaxDisposition(float $value)
 * @method float getBaseWeeeTaxRowDisposition()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBaseWeeeTaxRowDisposition(float $value)
 * @method float getDiscountTaxCompensationAmount()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setDiscountTaxCompensationAmount(float $value)
 * @method float getBaseDiscountTaxCompensationAmount()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setBaseDiscountTaxCompensationAmount(float $value)
 * @method null|bool getHasConfigurationUnavailableError()
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item setHasConfigurationUnavailableError(bool $value)
 * @method \Epicor\Customerconnect\Model\ArPayment\Quote\Item unsHasConfigurationUnavailableError()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @since 100.0.2
 */
class Item extends \Epicor\Customerconnect\Model\ArPayment\Quote\Item\AbstractItem implements \Epicor\Customerconnect\Api\Data\CartItemInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'ar_sales_quote_item';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'item';

    /**
     * Quote model object
     *
     * @var \Epicor\Customerconnect\Model\ArPayment\Quote
     */
    protected $_quote;

    /**
     * Item options array
     *
     * @var array
     */
    protected $_options = [];

    /**
     * Item options by code cache
     *
     * @var array
     */
    protected $_optionsByCode = [];

    /**
     * Not Represent options
     *
     * @var array
     */
    protected $_notRepresentOptions = ['info_buyRequest'];

    /**
     * Flag stating that options were successfully saved
     *
     */
    protected $_flagOptionsSaved;


    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     * @deprecated 100.2.0
     */
    protected $stockRegistry;

    /**
     * Serializer interface instance.
     *
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param Item\OptionFactory $itemOptionFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     *
     * @param \Epicor\Comm\Model\Serialize\Serializer\Json $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_localeFormat = $localeFormat;
        $this->stockRegistry = $stockRegistry;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Epicor\Comm\Model\Serialize\Serializer\Json::class);
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $productRepository,
            $priceCurrency,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Epicor\Customerconnect\Model\ArPayment\ResourceModel\Quote\Item::class);
    }

    /**
     * Quote Item Before Save prepare data process
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
//        $this->setIsVirtual($this->getProduct()->getIsVirtual());
//        if ($this->getQuote()) {
//            $this->setQuoteId($this->getQuote()->getId());
//        }
        return $this;
    }

    /**
     * Retrieve address model
     *
     * @return \Epicor\Customerconnect\Model\ArPayment\Quote\Address
     */
    public function getAddress()
    {
        if ($this->getQuote()->getItemsQty() == $this->getQuote()->getVirtualItemsQty()) {
            $address = $this->getQuote()->getBillingAddress();
        } else {
            $address = $this->getQuote()->getShippingAddress();
        }

        return $address;
    }

    /**
     * Declare quote model object
     *
     * @param   \Epicor\Customerconnect\Model\ArPayment\Quote $quote
     * @return $this
     */
    public function setQuote(\Epicor\Customerconnect\Model\ArPayment\Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        $this->setStoreId($quote->getStoreId());
        return $this;
    }

    /**
     * Retrieve quote model object
     *
     * @codeCoverageIgnore
     *
     * @return \Epicor\Customerconnect\Model\ArPayment\Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Prepare quantity
     *
     * @param float|int $qty
     * @return int|float
     */
    protected function _prepareQty($qty)
    {
        $qty = $this->_localeFormat->getNumber($qty);
        $qty = $qty > 0 ? $qty : 1;
        return $qty;
    }

    /**
     * Adding quantity to quote item
     *
     * @param float $qty
     * @return $this
     */
    public function addQty($qty)
    {
        /**
         * We can't modify quantity of existing items which have parent
         * This qty declared just once during add process and is not editable
         */
        if (!$this->getParentItem() || !$this->getId()) {
            $qty = $this->_prepareQty($qty);
            $this->setQtyToAdd($qty);
            $this->setQty($this->getQty() + $qty);
        }
        return $this;
    }

    /**
     * Declare quote item quantity
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $qty = $this->_prepareQty($qty);
        $oldQty = $this->_getData(self::KEY_QTY);
        $this->setData(self::KEY_QTY, $qty);

        $this->_eventManager->dispatch('ar_sales_quote_item_qty_set_after', ['item' => $this]);

        if ($this->getQuote() && $this->getQuote()->getIgnoreOldQty()) {
            return $this;
        }

        if ($this->getUseOldQty()) {
            $this->setData(self::KEY_QTY, $oldQty);
        }

        return $this;
    }

    /**
     * Retrieve option product with Qty
     *
     * Return array
     * 'qty'        => the qty
     * 'product'    => the product model
     *
     * @return array
     */
    public function getQtyOptions()
    {
        $qtyOptions = $this->getData('qty_options');
        if ($qtyOptions === null) {
            $productIds = [];
            $qtyOptions = [];
            foreach ($this->getOptions() as $option) {
                /** @var $option \Epicor\Customerconnect\Model\ArPayment\Quote\Item\Option */
                if (is_object($option->getProduct())
                    && $option->getProduct()->getId() != $this->getProduct()->getId()
                ) {
                    $productIds[$option->getProduct()->getId()] = $option->getProduct()->getId();
                }
            }

            foreach ($productIds as $productId) {
                $option = $this->getOptionByCode('product_qty_' . $productId);
                if ($option) {
                    $qtyOptions[$productId] = $option;
                }
            }

            $this->setData('qty_options', $qtyOptions);
        }

        return $qtyOptions;
    }

    /**
     * Set option product with Qty
     *
     * @codeCoverageIgnore
     *
     * @param array $qtyOptions
     * @return $this
     */
    public function setQtyOptions($qtyOptions)
    {
        return $this->setData('qty_options', $qtyOptions);
    }

    /**
     * Setup product for quote item
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        if ($this->getQuote()) {
            $product->setStoreId($this->getQuote()->getStoreId());
            $product->setCustomerGroupId($this->getQuote()->getCustomerGroupId());
        }
        $this->setData('product', $product)
            ->setProductId($product->getId())
            ->setProductType($product->getTypeId())
            ->setSku($this->getProduct()->getSku())
            ->setName($product->getName())
            ->setWeight($this->getProduct()->getWeight())
            ->setTaxClassId($product->getTaxClassId())
            ->setBaseCost($product->getCost());

        $stockItem = $product->getExtensionAttributes()->getStockItem();
        $this->setIsQtyDecimal($stockItem ? $stockItem->getIsQtyDecimal() : false);

        $this->_eventManager->dispatch(
            'ar_sales_quote_item_set_product',
            ['product' => $product, 'quote_item' => $this]
        );

        return $this;
    }

    /**
     * Check product representation in item
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @return  bool
     */
    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if (!$product || $itemProduct->getId() != $product->getId()) {
            return false;
        }

        /**
         * Check maybe product is planned to be a child of some quote item - in this case we limit search
         * only within same parent item
         */
        $stickWithinParent = $product->getStickWithinParent();
        if ($stickWithinParent) {
            if ($this->getParentItem() !== $stickWithinParent) {
                return false;
            }
        }

        // Check options
        $itemOptions = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();

        if (!$this->compareOptions($itemOptions, $productOptions)) {
            return false;
        }
        if (!$this->compareOptions($productOptions, $itemOptions)) {
            return false;
        }
        return true;
    }

    /**
     * Check if two options array are identical
     * First options array is prerogative
     * Second options array checked against first one
     *
     * @param array $options1
     * @param array $options2
     * @return bool
     */
    public function compareOptions($options1, $options2)
    {
        foreach ($options1 as $option) {
            $code = $option->getCode();
            if (in_array($code, $this->_notRepresentOptions)) {
                continue;
            }
            if (!isset($options2[$code]) || $options2[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        return true;
    }


    /**
     * Get item product type
     *
     * @return string
     */
    public function getProductType()
    {
        
        // $product should always exist or there will be an error in getProduct()
        return $this->_getData(self::KEY_PRODUCT_TYPE);
    }

    /**
     * Return real product type of item
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getRealProductType()
    {
        return $this->_getData(self::KEY_PRODUCT_TYPE);
    }

    /**
     * Convert Quote Item to array
     *
     * @param array $arrAttributes
     * @return array
     */
    public function toArray(array $arrAttributes = [])
    {
        $data = parent::toArray($arrAttributes);

        $product = $this->getProduct();
        if ($product) {
            $data['product'] = $product->toArray();
        }
        return $data;
    }

    /**
     * Initialize quote item options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        if (is_array($options)) {
            foreach ($options as $option) {
                $this->addOption($option);
            }
        }
        return $this;
    }

    /**
     * Get all item options
     *
     * @codeCoverageIgnore
     *
     * @return \Epicor\Customerconnect\Model\ArPayment\Quote\Item\Option[]
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Get all item options as array with codes in array key
     *
     * @codeCoverageIgnore
     *
     * @return array
     */
    public function getOptionsByCode()
    {
        return $this->_optionsByCode;
    }


    /**
     * Can specify specific actions for ability to change given quote options values
     * Example: cataloginventory decimal qty validation may change qty to int,
     * so need to change quote item qty option value.
     *
     * @param \Magento\Framework\DataObject $option
     * @param int|float|null $value
     * @return $this
     */
    public function updateQtyOption(\Magento\Framework\DataObject $option, $value)
    {
        $optionProduct = $option->getProduct();
        $options = $this->getQtyOptions();

        if (isset($options[$optionProduct->getId()])) {
            $options[$optionProduct->getId()]->setValue($value);
        }

        $this->getProduct()->getTypeInstance()->updateQtyOption(
            $this->getOptions(),
            $option,
            $value,
            $this->getProduct()
        );

        return $this;
    }

    /**
     * Remove option from item options
     *
     * @param string $code
     * @return $this
     */
    public function removeOption($code)
    {
        $option = $this->getOptionByCode($code);
        if ($option) {
            $option->isDeleted(true);
        }
        return $this;
    }

    /**
     * Register option code
     *
     * @param \Epicor\Customerconnect\Model\ArPayment\Quote\Item\Option $option
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addOptionCode($option)
    {
        if (!isset($this->_optionsByCode[$option->getCode()])) {
            $this->_optionsByCode[$option->getCode()] = $option;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('An item option with code %1 already exists.', $option->getCode())
            );
        }
        return $this;
    }

    /**
     * Get item option by code
     *
     * @param   string $code
     * @return  \Epicor\Customerconnect\Model\ArPayment\Quote\Item\Option || null
     */
    public function getOptionByCode($code)
    {
        if (isset($this->_optionsByCode[$code]) && !$this->_optionsByCode[$code]->isDeleted()) {
            return $this->_optionsByCode[$code];
        }
        return null;
    }

    /**
     * Checks that item model has data changes.
     * Call save item options if model isn't need to save in DB
     *
     * @return boolean
     */
    protected function _hasModelChanged()
    {
        if (!$this->hasDataChanges()) {
            return false;
        }

        return $this->_getResource()->hasDataChanged($this);
    }

    /**
     * Save item options
     *
     * @return $this
     */
    public function saveItemOptions()
    {
        foreach ($this->_options as $index => $option) {
            if ($option->isDeleted()) {
                $option->delete();
                unset($this->_options[$index]);
                unset($this->_optionsByCode[$option->getCode()]);
            } else {
                if (!$option->getItem() || !$option->getItem()->getId()) {
                    $option->setItem($this);
                }
                $option->save();
            }
        }

        $this->_flagOptionsSaved = true;
        // Report to watchers that options were saved

        return $this;
    }

    /**
     * Mar option save requirement
     *
     * @codeCoverageIgnore
     *
     * @param bool $flag
     * @return void
     */
    public function setIsOptionsSaved($flag)
    {
        $this->_flagOptionsSaved = $flag;
    }

    /**
     * Were options saved
     *
     * @codeCoverageIgnore
     *
     * @return bool
     */
    public function isOptionsSaved()
    {
        return $this->_flagOptionsSaved;
    }

    /**
     * Save item options after item saved
     *
     * @return \Epicor\Customerconnect\Model\ArPayment\Quote\Item
     */
    public function afterSave()
    {
        $this->saveItemOptions();
        return parent::afterSave();
    }

    /**
     * Clone quote item
     *
     * @return $this
     */
    public function __clone()
    {
        parent::__clone();
        $options = $this->getOptions();
        $this->_quote = null;
        $this->_options = [];
        $this->_optionsByCode = [];
        foreach ($options as $option) {
            $this->addOption(clone $option);
        }
        return $this;
    }

    /**
     * Returns formatted buy request - object, holding request received from
     * product view page with keys and options for configured product
     *
     * @return \Magento\Framework\DataObject
     */
    public function getBuyRequest()
    {
        $option = $this->getOptionByCode('info_buyRequest');
        $data = $option ? $this->serializer->unserialize($option->getValue()) : [];
        $buyRequest = new \Magento\Framework\DataObject($data);

        // Overwrite standard buy request qty, because item qty could have changed since adding to quote
        $buyRequest->setOriginalQty($buyRequest->getQty())->setQty($this->getQty() * 1);

        return $buyRequest;
    }

    /**
     * Sets flag, whether this quote item has some error associated with it.
     *
     * @param bool $flag
     * @return \Epicor\Customerconnect\Model\ArPayment\Quote\Item
     */
    protected function _setHasError($flag)
    {
        return $this->setData('has_error', $flag);
    }

    


    /**
     * Retrieves all error infos, associated with this item
     *
     * @return array
     */
    public function getErrorInfos()
    {
        return $this->_errorInfos->getItems();
    }

    /**
     * Removes error infos, that have parameters equal to passed in $params.
     * $params can have following keys (if not set - then any item is good for this key):
     *   'origin', 'code', 'message'
     *
     * @param array $params
     * @return $this
     */
    public function removeErrorInfosByParams($params)
    {
        $removedItems = $this->_errorInfos->removeItemsByParams($params);
        foreach ($removedItems as $item) {
            if ($item['message'] !== null) {
                $this->removeMessageByText($item['message']);
            }
        }

        if (!$this->_errorInfos->getItems()) {
            $this->_setHasError(false);
        }

        return $this;
    }

    /**
     * @codeCoverageIgnoreStart
     *
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->getData(self::KEY_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemId($itemID)
    {
        return $this->setData(self::KEY_ITEM_ID, $itemID);
    }

    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData(self::KEY_SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($sku)
    {
        return $this->setData(self::KEY_SKU, $sku);
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->getData(self::KEY_QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->getData(self::KEY_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        return $this->setData(self::KEY_PRICE, $price);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductType($productType)
    {
        return $this->setData(self::KEY_PRODUCT_TYPE, $productType);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteId()
    {
        return $this->getData(self::KEY_QUOTE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::KEY_QUOTE_ID, $quoteId);
    }


   
}
