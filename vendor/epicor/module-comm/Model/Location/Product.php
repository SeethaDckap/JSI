<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Location;


/**
 * Location product model
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * 
 * @method \Epicor\Comm\Model\Location\Product checkAndSetProductId(integer $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetLocationCode(string $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetStockStatus(string $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetFreeStock(float $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetMinimumOrderQty(float $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetMaximumOrderQty(float $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetLeadTimeDays(float $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetLeadTimeText(string $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetSupplierBrand(string $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetTaxCode(string $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetManufacturers(string $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetCreatedAt(datetime $value)
 * @method \Epicor\Comm\Model\Location\Product checkAndSetUpdatedAt(datetime $value)
 * 
 * @method \Epicor\Comm\Model\Location\Product setProductId(integer $value)
 * @method \Epicor\Comm\Model\Location\Product setLocationCode(string $value)
 * @method \Epicor\Comm\Model\Location\Product setStockStatus(string $value)
 * @method \Epicor\Comm\Model\Location\Product setFreeStock(float $value)
 * @method \Epicor\Comm\Model\Location\Product setMinimumOrderQty(float $value)
 * @method \Epicor\Comm\Model\Location\Product setMaximumOrderQty(float $value)
 * @method \Epicor\Comm\Model\Location\Product setLeadTimeDays(float $value)
 * @method \Epicor\Comm\Model\Location\Product setLeadTimeText(string $value)
 * @method \Epicor\Comm\Model\Location\Product setSupplierBrand(string $value)
 * @method \Epicor\Comm\Model\Location\Product setTaxCode(string $value)
 * @method \Epicor\Comm\Model\Location\Product setManufacturers(string $value)
 * @method \Epicor\Comm\Model\Location\Product setCreatedAt(datetime $value)
 * @method \Epicor\Comm\Model\Location\Product setUpdatedAt(datetime $value)
 * 
 * @method integer getProductId()
 * @method string getLocationCode()
 * @method string getStockStatus()
 * @method float getFreeStock()
 * @method float getMinimumOrderQty()
 * @method float getMaximumOrderQty()
 * @method float getLeadTimeDays()
 * @method string getLeadTimeText()
 * @method string getSupplierBrand()
 * @method string getTaxCode()
 * @method string getManufacturers()
 * @method datetime getCreatedAt()
 * @method datetime getUpdatedAt()
 */
class Product extends \Epicor\Common\Model\AbstractModel
{

    protected $_eventPrefix = 'ecc_location_product';
    protected $_eventObject = 'location_product';
    private $_currencies;
    private $_deleteCurrencies = array();

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Location\Product\Currency\CollectionFactory
     */
    protected $commResourceLocationProductCurrencyCollectionFactory;

    /**
     * @var \Epicor\Comm\Model\Location\Product\CurrencyFactory
     */
    protected $commLocationProductCurrencyFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\ResourceModel\Location\Product\Currency\CollectionFactory $commResourceLocationProductCurrencyCollectionFactory,
        \Epicor\Comm\Model\Location\Product\CurrencyFactory $commLocationProductCurrencyFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->commResourceLocationProductCurrencyCollectionFactory = $commResourceLocationProductCurrencyCollectionFactory;
        $this->commLocationProductCurrencyFactory = $commLocationProductCurrencyFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    protected function _construct()
    {
        // initialize resource model
        $this->_init('Epicor\Comm\Model\ResourceModel\Location\Product');
    }

    /**
     * Gets all location data for this product
     * 
     * @return array
     */
    public function getCurrencies()
    {
        if (is_null($this->_currencies)) {
            $currencies = $this->commResourceLocationProductCurrencyCollectionFactory->create();
            /* @var $currencies \Epicor\Comm\Model\ResourceModel\Location\Product\Currency\Collection */

            $currencies->addFieldToFilter('product_id', $this->getProductId());
            //$currencies->addFieldToFilter('location_code', $this->getLocationCode());
            // Adding below for case sensitive check of location code
            $currencies->getSelect()->where('BINARY location_code = (?)', $this->getLocationCode());

            foreach ($currencies->getItems() as $currency) {
                /* @var $currency \Epicor\Comm\Model\Location\Product\Currency */
                $this->_currencies[$currency->getCurrencyCode()] = $this->commLocationProductCurrencyFactory->create()->load($currency->getId());
            }
            if (!is_array($this->_currencies)) {
                $this->_currencies = array();
            }
        }

        return $this->_currencies;
    }

    /**
     * 
     * @param string $currency_code
     * @return \Epicor\Comm\Model\Location\Product\Currency
     */
    public function getCurrency($currency_code = null)
    {
        $currencyData = false;
        $data = $this->getCurrencies();
        if (is_array($data)) {
            if (array_key_exists($currency_code, $data)) {
                $currencyData = $data[$currency_code];
            }
        }
        return $currencyData;
    }

    /**
     * Sets the currencies for this product
     * 
     * @param array $currencies
     * @param  array $storesdefaultCurrency
     */
    public function setCurrencies($currencies,$storesdefaultCurrency = array())
    {
        $currentCurrencies = $this->getCurrencies();
        $newCurrencies = array();
        if($currencies){
            foreach ($currencies as $currency) {
                if(!empty($storesdefaultCurrency)){
                    if(in_array($currency->getCurrencyCode(),$storesdefaultCurrency)){
                        $this->setCurrencyData($currency->getCurrencyCode(), $currency);
                        $newCurrencies[] = $currency->getCurrencyCode();
                    }
                }else{
                    $this->setCurrencyData($currency->getCurrencyCode(), $currency);
                    $newCurrencies[] = $currency->getCurrencyCode();
                }
            }
        }
        foreach ($currentCurrencies as $currency) {
            /* @var $currency \Epicor\Comm\Model\Location\Product\Currency */
            if (!in_array($currency->getCurrencyCode(), $newCurrencies)) {
                $this->deleteCurrency($currency->getCurrencyCode());
            }
        }
    }

    /**
     * Sets the data for a currency
     * 
     * @param string $currencyCode
     * @param \Epicor\Common\Model\Xmlvarien $data
     */
    public function setCurrencyData($currencyCode, $data)
    {
        /* @var $currency \Epicor\Comm\Model\Location\Product\Currency */
        if (isset($this->_currencies[$currencyCode])) {
            $currency = $this->_currencies[$currencyCode];
        } else {
            $currency = $this->commLocationProductCurrencyFactory->create();
        }

        $currency->checkAndSetProductId($this->getProductId());
        $currency->checkAndSetLocationCode($this->getLocationCode());
        $currency->checkAndSetCurrencyCode($currencyCode);
        $currency->checkAndSetBasePrice($data->getBasePrice());
        $currency->checkAndSetCostPrice($data->getCostPrice());

        if ($currency->hasDataChanges()) {
            $this->_hasDataChanges = true;
        }

        $this->_currencies[$currencyCode] = $currency;
    }

    /**
     * Sets the data for a currency
     * 
     * @param string $currencyCode
     * @param \Epicor\Comm\Model\Location\Product\Currency $currency
     */
    public function setCurrencyObject($currencyCode, $currency)
    {
        $this->_currencies[$currencyCode] = $currency;
    }

    /**
     * Set Currency Data from a Location_Product_Currency Model
     * 
     * @param  $currency \Epicor\Comm\Model\Location\Product\Currency
     */
    public function setCurrency($currency)
    {
        $this->setCurrencyData($currency->getCurrencyCode(), $currency);
    }

    /**
     * Moves a currency to the deleted array for deletion when product saves
     * 
     * @param string $currencyCode
     */
    public function deleteCurrency($currencyCode)
    {
        if (isset($this->_currencies[$currencyCode])) {
            $this->_deleteCurrencies[] = $this->_currencies[$currencyCode];
            unset($this->_currencies[$currencyCode]);
        }
    }

    /**
     * Process currencies after the product is saved
     */
    public function afterSave()
    {
        if (is_array($this->_currencies)) {
            foreach ($this->_currencies as $currency) {
                /* @var $currency \Epicor\Comm\Model\Location\Product\Currency */
                $currency->save();
            }
        }

        if (is_array($this->_deleteCurrencies)) {
            foreach ($this->_deleteCurrencies as $currency) {
                /* @var $currency \Epicor\Comm\Model\Location\Product\Currency */
                if (!$currency->isObjectNew()) {
                    $currency->delete();
                }
            }
        }

        parent::afterSave();
    }

    public function beforeDelete()
    {
        $currencies = $this->getCurrencies();
        if (is_array($currencies)) {
            foreach ($currencies as $currency) {
                /* @var $currency \Epicor\Comm\Model\Location\Product\Currency */
                $currency->delete();
            }
        }
        parent::beforeDelete();
    }

    public function isInStock()
    {
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */

        $qty = $helper->qtyRounding($this->getFreeStock());

        return $qty > 0 ? true : $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/msq_request/products_always_in_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
