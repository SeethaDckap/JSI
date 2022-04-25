<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model;


/**
 * 
 * @method getEccBsvGoodsTotal()
 * @method setEccBsvGoodsTotal()
 * @method getEccBsvGoodsTotalInc
 * @method setEccBsvGoodsTotalInc
 * @method getEccBsvCarriageAmount()
 * @method setEccBsvCarriageAmount()
 * @method getEccBsvCarriageAmountInc()
 * @method setEccBsvCarriageAmountInc()
 * @method getEccBsvGrandTotal()
 * @method setEccBsvGrandTotal()
 * @method getEccBsvGrandTotalInc()
 * @method setEccBsvGrandTotalInc()
 * @method getEccGorSent()
 * @method setEccGorSent()
 * @method getEccGorMessage()
 * @method setEccGorMessage()
 * @method getEccErpOrderNumber()
 * @method setEccErpOrderNumber()
 * @method getEccLastSodUpdate()
 * @method setEccLastSodUpdate()
 * @method getEccRequiredDate()
 * @method setEccRequiredDate()
 * @method getEccInitialGrandTotal()
 * @method setEccInitialGrandTotal()
 * @method getEccDeviceUsed()
 * @method setEccDeviceUsed()
 * @method getEccCustomerOrderRef()
 * @method setEccCustomerOrderRef()
 * @method getEccShipStatusErpcode()
 * @method setEccShipStatusErpcode()
 * @method getEccAdditionalReference()
 * @method setEccAdditionalReference()
 * @method getEsdmTransactionId()
 * @method setEsdmTransactionId()
 * @method getVerifoneTransactionId()
 * @method setVerifoneTransactionId()
 * @method setManualState(string $state)
 * @method string getManualState()
 * @method setManualStatus(string $status)
 * @method string getManualStatus()
 * 
 */
class Order extends \Magento\Sales\Model\Order
{

    protected $_emailSentThisTime = false;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\SalesRep\Helper\Data
     */
    protected $salesRepHelper;

    /**
     * @var \Epicor\Common\Helper\Cart
     */
    protected $commonCartHelper;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $quoteQuoteItemFactory;

    /**
     * @var \Epicor\Comm\Helper\Sales\Order
     */
    protected $commSalesOrderHelper;

    /**
     * @var int
     */
    private $decimalPrecision;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Sales\Api\InvoiceManagementInterface $invoiceManagement,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Sales\Model\Order\Status\HistoryFactory $orderHistoryFactory,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory $paymentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory $historyCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $memoCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productListFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Epicor\Common\Helper\Cart $commonCartHelper,
        \Magento\Quote\Model\Quote\ItemFactory $quoteQuoteItemFactory,
        \Epicor\Comm\Helper\Sales\Order $commSalesOrderHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commHelper = $commHelper;
        $this->customerSession = $customerSession;
        $this->salesRepHelper = $salesRepHelper;
        $this->commonCartHelper = $commonCartHelper;
        $this->quoteQuoteItemFactory = $quoteQuoteItemFactory;
        $this->commSalesOrderHelper = $commSalesOrderHelper;
        $this->decimalPrecision = $commHelper->getProductPricePrecision();
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $timezone,
            $storeManager,
            $orderConfig,
            $productRepository,
            $orderItemCollectionFactory,
            $productVisibility,
            $invoiceManagement,
            $currencyFactory,
            $eavConfig,
            $orderHistoryFactory,
            $addressCollectionFactory,
            $paymentCollectionFactory,
            $historyCollectionFactory,
            $invoiceCollectionFactory,
            $shipmentCollectionFactory,
            $memoCollectionFactory,
            $trackCollectionFactory,
            $salesOrderCollectionFactory,
            $priceCurrency,
            $productListFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * Retrieve order reorder availability
     *
     * @param bool $ignoreSalable
     * @return bool
     */
    protected function _canReorder($ignoreSalable = false)
    {
        $helper = $this->commHelper;

        $checkSalrepAccount = $this->checkSalerepErpAccount();




        /* @var $helper Epicor_Comm_Helper_Data */

        if ($helper->isFunctionalityDisabledForCustomer('cart')) {
            return false;
        } else {
            if ($checkSalrepAccount == "hide") {
                return false;
            } else {
                return true;
            }
        }
    }

    protected function checkSalerepErpAccount()
    {
        $customerSession = $this->customerSession;
        $customer = $customerSession->getCustomer();
        $isSaleRep = $customer->isSalesRep();
        $returnVals = array();
        /* @var $helper \Epicor\SalesRep\Helper\Data */
        if ($isSaleRep) {
            $orderErpAccountId = $this->getEccErpAccountId();
            $helperSalesRep = $this->salesRepHelper;
            $currentErpAccount = $helperSalesRep->getErpAccountInfo();
            $getCurrentErpId = $currentErpAccount->getId();
            if ($orderErpAccountId == $getCurrentErpId) {
                return $returnVals = "show";
            } else {
                return $returnVals = "hide";
            }
        }
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $realOrderId = $this->getRealOrderId();
        $this->setRealOrderId($realOrderId);
    }

    protected function _checkState()
    {
        parent::_checkState();
        if ($this->getManualState()) {
            $this->setData('state', $this->getManualState());
        }
        if ($this->getManualStatus()) {
            $this->setData('status', $this->getManualStatus());
        }
    }

    public function addLine($product, $options)
    {
        return $this->addOrUpdateLine($product, $options, true);
    }

    public function addOrUpdateLine($product, $options, $newLine = false)
    {
        $helper = $this->commonCartHelper;
        /* @var $helper Epicor_Common_Helper_Cart */

        if ($newLine) {
            $options['new_line'] = 1;
        }

        $helper->processQuoteLine($this, $product, $options);

        return $this;
    }

    public function findItem($product, $options)
    {
        $locationCode = isset($options['location_code']) ? $options['location_code'] : '';

        if (is_array($locationCode)) {
            $locationCode = isset($locationCode[$product->getId()]) ? $locationCode[$product->getId()] : false;
        }

        $productItem = $this->quoteQuoteItemFactory->create();
        foreach ($this->getAllItems() as $item) {
            /* @var $item \Magento\Sales\Model\Order\Item */
            $match = true;
            
            if (isset($options['super_attribute'])) {
                if ($product && $product->getTypeId() == 'configurable') {
                    $productChild = $product->getTypeInstance()->getProductByAttributes($options['super_attribute'], $product);
                    if ($item->getSku() != $productChild->getSku()) {
                        $match = false;
                    }
                }
            }
            
            if ($item->getProductId() != $product->getId()) {
                $match = false;
            }

            if (!empty($locationCode) && $item->getEccLocationCode() != $locationCode) {
                $match = false;
            }

            if ($match) {
                $productItem = $item;
                break;
            }
        }

        return $productItem;
    }

    public function getRealOrderId()
    {
        $helper = $this->commSalesOrderHelper;
        /* @var $helper Epicor_Comm_Helper_Sales_Order */

        if ($helper->showBothOrderNumbers($this)) {
            $orderNumber = $this->getIncrementId();
            if ($this->getEccErpOrderNumber()) {
                //M1 > M2 Translation Begin (Rule 55)
                //$orderNumber .= $helper->__(' (ERP #%s)', $this->getEccErpOrderNumber());
                $orderNumber .= __(' (ERP #%1)', $this->getEccErpOrderNumber());
                //M1 > M2 Translation End
            }
        } elseif ($helper->showErpOrderNumberOnly($this)) {
            $orderNumber = $this->getEccErpOrderNumber();
        } else {
            $orderNumber = $this->getIncrementId();
        }

        return $orderNumber;
    }

    /**
     * Get formatted price value including order currency rate to order website currency
     *
     * @param float $price
     * @param bool $addBrackets
     * @return string
     */
    public function formatPrice($price, $addBrackets = false)
    {
        return $this->formatPricePrecision($price, $this->decimalPrecision, $addBrackets);
    }

}
