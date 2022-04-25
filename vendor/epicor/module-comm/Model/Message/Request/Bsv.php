<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Message\Request;

use Epicor\Comm\Helper\BsvAndGor;
use \Epicor\Comm\Model\RepriceFlag as RepriceFlag;
use Epicor\Quotes\Model\ResourceModel\Quote as EccQuote;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

/**
 * Request BSV - Basket Valuation
 *
 * Request a valuation from the ERP for the specified items
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 * @method setSaveChanges
 * @method setPromotions
 *
 * @method setShippingAddress(Epicor_Comm_Model_Quote_Address $address)
 * @method Epicor_Comm_Model_Quote_Address getShippingAddress()
 */
class Bsv extends \Epicor\Comm\Model\Message\Request
{


    /**
     * Message Type
     */
    const MESSAGE_TYPE = 'BSV';
    /**
     * Quote
     *
     * @var \Epicor\Comm\Model\Quote
     */
    protected $_quote;
    protected $_lineItems;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Epicor\Comm\Helper\Messaging\Customer
     */
    protected $commMessagingCustomerHelper;

    /**
     * @var \Magento\Tax\Model\ClassModelFactory
     */
    protected $taxClassModelFactory;

    /**
     * @var \Epicor\Common\Helper\Cart
     */
    protected $commonCartHelper;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteQuoteFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    protected $arpaymentsHelper;

    /**
     * @var \Epicor\Common\Helper\Cart
     */
    protected $commHelper;

    /** @var \Epicor\Comm\Helper\BsvAndGor */
    private $bsvAndGorHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var EccQuote
     */
    private $eccQuote;

    /**
     * Construct object and set message type.
     */
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Common\Helper\Data $commonHelper,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Epicor\Comm\Helper\Messaging\Customer $commMessagingCustomerHelper,
        \Magento\Tax\Model\ClassModelFactory $taxClassModelFactory,
        \Epicor\Common\Helper\Cart $commonCartHelper,
        \Magento\Quote\Model\QuoteFactory $quoteQuoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Epicor\Comm\Helper\BsvAndGor $bsvAndGor = null,
        ResourceConnection $resourceConnection = null,
        EccQuote $eccQuote = null
    )
    {

        $this->commonHelper = $commonHelper;
        $this->customerFactory = $customerFactory;
        $this->commMessagingCustomerHelper = $commMessagingCustomerHelper;
        $this->taxClassModelFactory = $taxClassModelFactory;
        $this->commonCartHelper = $commonCartHelper;
        $this->quoteQuoteFactory = $quoteQuoteFactory;
        $this->registry = $context->getRegistry();
        $this->catalogProductFactory = $context->getCatalogProductFactory();
        $this->checkoutSession = $checkoutSession;
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->commHelper = $context->getCommHelper();

        parent::__construct($context, $resource, $resourceCollection, $data);

        $this->setMessageType('BSV');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setMessageCategory(self::MESSAGE_CATEGORY_ORDER);
        $this->setConfigBase('epicor_comm_enabled_messages/bsv_request/');
        $this->_products = array();
        $this->_uomSeparator = $this->commonHelper->getUOMSeparator();
        unset($this->error_status_codes['P']);
        $this->error_status_codes['P'] = 'Products Not On File';

        $this->bsvAndGorHelper = $bsvAndGor;
        $this->quoteRepository = $quoteRepository;
        $this->resourceConnection = $resourceConnection ?: ObjectManager::getInstance()->get(ResourceConnection::class);
        $this->eccQuote = $eccQuote ?: ObjectManager::getInstance()->get(EccQuote::class);
    }

    public function getQuote()
    {
        return $this->_quote;
    }

    public function getLineItem($itemId)
    {
        return isset($this->_lineItems[$itemId]) ? $this->_lineItems[$itemId] : false;
    }

    /**
     * Sets the quote for this Order
     * For funtions used an order can also be set.
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function setQuote(&$quote)
    {
        $this->_quote = $quote;
        if ($quote instanceof \Magento\Sales\Model\Order || $quote instanceof \Epicor\Comm\Model\Order) {
            $subject = 'Basket Quote ID: ' . $quote->getQuoteId();
        } else {
            $subject = 'Basket Quote ID: ' . $quote->getId();
        }
        $this->setMessageSecondarySubject($subject . '<br>Erp Quote ID: ' . $quote->getEccBasketErpQuoteNumber());
        $this->setStoreId($quote->getStoreId());
        return $this;
    }

    function strposa($haystack, $needle, $offset = 0)
    {
        if (!is_array($needle)) $needle = array($needle);
        foreach ($needle as $query) {
            if (strpos($haystack, $query, $offset) !== false) return true; // stop on first true result
        }
        return false;
    }

    public function buildRequest()
    {
        return $this->buildObjectRequest();
    }

    public function buildObjectRequest()
    {
        $helper = $this->getHelper();

        /* @var $customer_helper \Epicor\Comm\Helper\Messaging\Customer */
        $customer_helper = $this->commMessagingCustomerHelper;

        $noBsv = $this->registry->registry('configurator-no-bsv');
        // If there is a call from bsv when there is no quote id (request from cim and cdm please dont send the bsv).
        if ($noBsv) {
            return false;
        }

        if (($this->registry->registry('msq_sent') && !$this->registry->registry('bsv_sent'))) {
            $this->registry->unregister('msq_sent');
            $this->registry->unregister('QuantityValidatorObserver');
            $this->registry->register('QuantityValidatorObserver', 1);
            $this->registry->register('dont_send_bsv', 1);
            $quoteId = $this->_quote->getId();
            if ($this->_quote instanceof \Magento\Sales\Model\Order
                || $this->_quote instanceof \Epicor\Comm\Model\Order
            ) {
                $quoteId = $this->_quote->getQuoteId();
            }
            $this->cleanEmptyQuote($this->_quote);
            $this->_quote->getShippingAddress()->setCollectShippingRates(true);
            $this->_quote->collectTotals()->save();
            $this->registry->unregister('QuantityValidatorObserver');
        }

        $erpAccountId = $this->_quote->getEccErpAccountId() ?: $this->getCustomerGroupId();
        $erpAccount = $helper->getErpAccountInfo($erpAccountId);
        /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
        $ignoreArray = array('/customer/account/loginPost/', '/customer/account/registerpost/', '/customer/account/createpost/', '/b2b/portal/registerpost/', '/customer/account/confirm/');
        //if no quote and customer has not just logged in (customer could have placed order and logged out), or created an account, flag an error
        if (!$this->_quote->getId() && !in_array($this->request->getServer('REQUEST_URI'), $ignoreArray)) {
            $this->setConnectionSuccessful(true);
            return 'Missing Quote Id' . $this->_quote->getId();
        }
        if (empty($erpAccount)) {
            $this->setConnectionSuccessful(true);
            return 'Missing ERP account Id:- ' . $erpAccountId;
        }

        $arPaymentsPage = $this->arpaymentsHelper->checkArpaymentsPage();
        if ($this->_quote->getArpaymentsQuote() || $arPaymentsPage) {
            return false;
        }

        $fullAccountNumber = $erpAccount->getErpCode();
        $accountNumber = $erpAccount->getAccountNumber();
        $session = $this->genericFactory->create();
        /* @var $session Magee\Core\Model\Session */
        if (!$accountNumber) {
            $this->setConnectionSuccessful(true);
            return 'Missing account number (account Id:- ' . $erpAccountId . ')';
        } else {
            $shippingAddress = $this->getCustomerShippingAddress();
            /* @var $shippingAddress \Epicor\Comm\Model\Quote\Address */
            $shippingAddressCode = $helper->getErpAddress($shippingAddress->getCustomerAddressId(), $fullAccountNumber);
            $delivery_method = $this->getHelper()->getShippingMethodMapping($this->_quote->getShippingMethod() ?: $shippingAddress->getShippingMethod());
            $prevent_reprice = $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/bsv_request/bsv_prevent_repricing', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
            $default_shipping_days = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/daystoship', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
            $dateRequired = in_array($this->_quote->getEccRequiredDate(), array('0000-00-00', null, '')) ? $this->getHelper()->getLocalDate(strtotime(date('Y-m-d', strtotime(" +$default_shipping_days day"))), \IntlDateFormatter::LONG) : $this->getHelper()->getLocalDate(strtotime(date('Y-m-d', strtotime($this->_quote->getEccRequiredDate()))), \IntlDateFormatter::LONG);
            $charge = $shippingAddress->getBaseShippingAmount() ?: $this->_quote->getBaseShippingAmount() ?: 0;

            $sqlQuoteId = $this->_quote->getId();
            if ($this->_quote instanceof \Magento\Sales\Model\Order || $this->_quote instanceof \Epicor\Comm\Model\Order) {
                $sqlQuoteId = $this->_quote->getQuoteId();
            }
            $tableName           = $this->resourceConnection->getTableName('quote_address');
            $column              = [
                'base_shipping_incl_tax',
                'shipping_method',
            ];
            $connection          = $this->resourceConnection->getConnection();
            $sqlQuery            = $connection->select()
                ->from($tableName, $column)
                ->where('address_type = ?', 'shipping')
                ->where('quote_id = ?', $sqlQuoteId);
            $baseShippingInclTax = $connection->fetchRow($sqlQuery);

            $chargeInc = $baseShippingInclTax['base_shipping_incl_tax'] ?:
                $shippingAddress->getBaseShippingInclTax() ?: $this->_quote->getBaseShippingInclTax() ?: 0;

            if ($baseShippingInclTax['shipping_method']
                && $baseShippingInclTax['shipping_method'] != $shippingAddress->getShippingMethod()
            ) {
                $chargeInc = $charge;
            }

            if (!$baseShippingInclTax['shipping_method'] || !$shippingAddress->getShippingMethod()) {
                $chargeInc = $baseShippingInclTax['base_shipping_incl_tax'];
            }

            if ($chargeInc == 0.0000) {
                $chargeInc = $charge;
            }

            $reference = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/gor_order_prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
            $reference .= $this->_quote->getIncrementId();
            $customerReference = $this->_quote->getEccCustomerOrderRef() ? $this->_quote->getEccCustomerOrderRef() : $reference;
            $currency = $this->getHelper()->getCurrencyMapping($this->_quote->getStoreCurrencyCode());
            $taxExemptReference = $this->commHelper->isTaxExemptionAllowed($this->_quote->getEccErpAccountId());

            if ($this->getPaymentMethod()) {
                $prevent_reprice = $helper->getOrderRepriceValue($this->_quote, $this->getPaymentMethod(), 'BSV');
            } else {
                $prevent_reprice = $helper->getOrderRepriceValue($this->_quote, '', 'BSV');
            }

            $grandTotal = 0;
            if ($this->_quote->getBaseGrandTotal() && !$this->_quote->getIsMultiShipping()) {
                $tax = $this->_quote->getBaseTaxAmount() ?: $shippingAddress->getBaseTaxAmount();
                $grandTotal = $this->_quote->getBaseGrandTotal() - $tax;
            } else if ($shippingAddress->getBaseGrandTotal()) {
                $grandTotal = ($shippingAddress->getBaseGrandTotal() - $shippingAddress->getBaseTaxAmount());
            }
            $grandTotalInc = $shippingAddress->getBaseGrandTotal() ?: $this->_quote->getBaseGrandTotal() ?: 0;

            if ($this->_quote->getIsMultiShipping()) {
                $discount = $shippingAddress->getBaseSubtotal() - $shippingAddress->getBaseSubtotalWithDiscount();
            } else {
                $discount = $this->_quote->getBaseDiscountAmount() ?: $shippingAddress->getBaseDiscountAmount();
            }

            $quotePrefix = $this->_quote->getEccQuoteId() ? $this->scopeConfig->getValue('epicor_quotes/general/prefix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()) : '';

            $calculateDiscount = abs($discount);

            //Fix for Coupon -- Flat rate & UPS & USPS --
            //All the functionalites changed after our 2.3.1 changes
            //https://github.com/magento/magento2/issues/16388
            //https://github.com/magento/magento2/issues/14206
            //There are lot of issues in this area(only with free shippin items). We fixed only for flatrate. UPS
            if ($shippingAddress->getShippingMethod() == "flatrate_flatrate") {
                $registries = $this->registry->registry('flat_rate_applied_zero');
                if ($registries == "0") {
                    $charge = 0;
                    $chargeInc = 0;
                } else {
                    $charge = $registries;
                    $chargeInc = $chargeInc;
                }
            }

            if ($shippingAddress->getFreeShipping() == "1" && $this->_quote->getCouponCode()) {
                $this->registry->unregister('flat_rate_applied_zero');
                $this->registry->register('flat_rate_applied_zero', 1);
                $charge = 0;
                $chargeInc = 0;
            }

            $data = $this->getMessageTemplate();
            $data['messages']['request']['body'] = array_merge($data['messages']['request']['body'], array(
                'accountNumber' => $accountNumber,
                'currencyCode' => $currency,
                'delivery' => array(
                    'deliveryAddress' => $helper->formatAddress($shippingAddress, 'shipping'),
                    'charge' => $charge,
                    'methodCode' => $delivery_method,
                ),
                'order' => array(
                    '_attributes' => array(
                        'preventRepricing' => $prevent_reprice ? 'Y' : 'N'
                    ),
                    'orderReference' => $reference,
                    'customerReference' => $customerReference,
                    'taxExemptReference' => ($taxExemptReference) ? $this->_quote->getEccTaxExemptReference() : null,
                    'additionalReference' => $this->_quote->getEccAdditionalReference() ?: null,
                    'shipStatus' => $this->_quote->getEccShipStatusErpcode() ?: null,
                    'dateOrdered' => $helper->getFormattedDateTime(),
                    'dateRequired' => $dateRequired, // Should this be the same as dateOrdered?
                    'quoteNumber' => $this->_quote->getEccBasketErpQuoteNumber(),
                    'eccGqrQuoteNumber' => $this->eccQuote->getWebReferenceId($this->_quote->getEccQuoteId()),
                    'erpGqrQuoteNumber' => $this->_quote->getEccErpQuoteId(),
                    'goodsTotal' => $this->checkValueDataType($shippingAddress->getBaseSubtotal() ?: $this->_quote->getBaseSubtotal() ?: 0),
                    'goodsTotalInc' => $this->checkValueDataType($shippingAddress->getBaseSubtotalTotalInclTax() ?: $this->_quote->getBaseSubtotalInclTax() ?: 0),
                    'discountAmount' => $this->checkValueDataType(abs($discount)),
                    'carriageAmount' => $this->checkValueDataType($charge),
                    'carriageAmountInc' => $this->checkValueDataType($chargeInc),
                    'grandTotal' => $this->checkValueDataType($grandTotal),
                    'grandTotalInc' => $this->checkValueDataType($grandTotalInc),
                    'contractCode' => $this->_quote->getEccContractCode()
                ),
                'lines' => array(
                    'line' => array(),
                ),
            ));

            if ($this->_quote->getIsMultiShipping()) {
                $items = $shippingAddress->getAllItems();
            } else {
                $items = $this->_quote->getAllItems();
            }

            $lineNumber = 1;
            $bGoodstotal = 0;
            $bGoodstotalInc = 0;
            foreach ($items as $item) {
                $lineDiscount = 0;
                /* @var $item Mage_Sales_Model_Quote_Item */
                /* ToDo Add code / options to ignore bundle products */
                // if($item->getProductType() == 'bundle')
                if (!$item->isDeleted() && $item->getParentItemId() == null && ($item->getParentId() == null || $this->getPromotions())) {
                    //$partNo = $helper->stripProductCodeUOM($item->getSku());
                    $uomArr = $helper->splitProductCode($item->getSku());
                    $productSku = $uomArr[0];
                    if (!$item->getProduct()) {
                        $this->setConnectionSuccessful(true);
                        return 'Missing Product (Sku) ' . $item->getSku();
                    }
                    $uomCode = $this->commonHelper->getProductUom($uomArr, $item);
                    // $productSku = $helper->stripProductCodeUOM($item->getSku());
                    $quantity = $item->getQty() == null ? $item->getQtyOrdered() : $item->getQty();

                    $tax_class = $this->taxClassModelFactory->create()->load($item->getProduct()->getTaxClassId());
                    //$item->calcRowTotal();

                    $attributes = array();

                    $productOptions = $helper->getItemProductOptions($item);

                    if ($item->getProduct()->getMsqAttributes()) {
                        $attributes = $this->buildProductAttributes($item->getProduct());
                    } elseif (!empty($productOptions) && !empty($productOptions['options'])) {
                        if (is_array($productOptions['options'])) {
                            $attributes['attribute'] = array();
                            foreach ($productOptions['options'] as $option) {
                                if (!in_array($option['option_type'], array(
                                    'ewa_description',
                                    'ewa_title',
                                    'ewa_short_description',
                                    'ewa_sku'
                                ))) {
                                    $attributes['attribute'][] = array(
                                        'description' => $option['option_type'] == 'ewa_code' ? 'Ewa Code' : $option['label'],
                                        'value' => $option['value']
                                    );
                                }
                            }
                        }
                    } else if (isset($productOptions['info_buyRequest']['options']['ewa_code'])) {
                        $attributes['attribute'][] = array(
                            'description' => 'Ewa Code',
                            'value' => $productOptions['info_buyRequest']['options']['ewa_code']
                        );
                    }

                    if ($this->scopeConfig->isSetFlag('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId())) {
                        $itemPrice = $item->getBasePriceInclTax();
                    } else {
                        $itemPrice = $item->getBasePrice();
                    }
                    //Rule for Fixed Amount not reflected in Product Search Results
                    //if it is higher than "normal" price
                    //calculate discount if the final price is higher than the customer price
                    //customer price - promotion price.
                    if ($item->getHigherDiscount()) {
                        $lineDiscount = $item->getOrdinaryCustomerAmount() - $item->getPromotionalAmount();
                    } else if (!is_null($item->getEccOriginalPrice())) {
                        $lineDiscount = $item->getEccOriginalPrice() - $itemPrice;
                    }

                    $decimalPlaces = $helper->getDecimalPlaces($item->getProduct());
                    $data['messages']['request']['body']['lines']['line'][] = array(
                        '_attributes' => array(
                            'number' => $lineNumber,
                            'itemId' => $item->getId(),
                            'preventRepricing' =>
                                RepriceFlag::getItemRepricingFlag($lineDiscount, $quantity, $prevent_reprice)
                        ),
                        'productCode' => $productSku,
                        'unitOfMeasureCode' => $uomCode,
                        'contractCode' => $item->getEccContractCode(),
                        'locationCode' => $item->getEccLocationCode(),
                        'decimalPlaces' => $decimalPlaces,
                        'quantity' => $helper->qtyRounding($quantity, $decimalPlaces),
                        'price' => $this->checkValueDataType($item->getBasePrice()),
                        'priceInc' =>$this->checkValueDataType($item->getBasePriceInclTax()),
                        'lineValue' => $this->checkValueDataType($item->getBaseRowTotal()),
                        'lineValueInc' =>$this->checkValueDataType($item->getBaseRowTotalInclTax()),
                        'lineDiscount' => $this->checkValueDataType($lineDiscount * $quantity),
                        'taxCode' => $tax_class->getClassName(),
                        'dateRequired' => $dateRequired,
                        'eccGqrLineNumber' => $item->getEccGqrLineNumber(),
                        'attributes' => $attributes,
                    );

                    $bGoodstotal += $item->getBaseRowTotal();
                    $bGoodstotalInc += $item->getBaseRowTotalInclTax();

                    $this->_lineItems[$item->getId()] = $item;
                }
                ++$lineNumber;
            }
            if (count($data['messages']['request']['body']['lines']['line']) == 0)
                $data['messages']['request']['body']['lines'] = array();


            // We added this condition because after 2.3.1 and less than 2.3.1 fix calculations are not working as expected
            $bgrandTotal = $bGoodstotal + $charge - $calculateDiscount;
            $bgrandTotalInc = $bGoodstotalInc + $chargeInc - $calculateDiscount;

            $data['messages']['request']['body']['order']['goodsTotal'] = $this->checkValueDataType($bGoodstotal ?: 0);
            $data['messages']['request']['body']['order']['goodsTotalInc'] = $this->checkValueDataType($bGoodstotalInc ?: 0);
            $data['messages']['request']['body']['order']['grandTotal'] = $this->checkValueDataType($bgrandTotal);
            $data['messages']['request']['body']['order']['grandTotalInc'] = $this->checkValueDataType($bgrandTotalInc);

            $this->setOutXml($data);
            return true;
        }
    }

    /**
     * @return bool|\Magento\Customer\Model\Address|mixed|null
     */
    private function getCustomerDefaultShippingAddress()
    {
        return $this->bsvAndGorHelper
            ->getCustomerDefaultShippingAddress($this->customerSession, $this->getQuoteAddresses());
    }

    /**
     * @return mixed
     */
    private function getQuoteAddresses()
    {
        if ($this->_quote instanceof \Epicor\Comm\Model\Quote) {
            return $this->_quote->getAddresses();
        }
    }

    /**
     * @return bool|\Magento\Customer\Model\Address|mixed|null
     */
    private function getCustomerShippingAddress()
    {
        return $this->getQuoteShippingAddress() ?: $this->getCustomerDefaultShippingAddress();
    }

    /**
     * @return \Epicor\Comm\Model\Quote\Address
     */
    private function getQuoteShippingAddress()
    {
        return $this->_quote->getIsMultiShipping() ? $this->getShippingAddress() : $this->_quote->getShippingAddress();
    }

    private function buildProductAttributes($product)
    {
        $attributes = array();
        if ($product->getMsqAttributes()) {
            foreach ($product->getMsqAttributes() as $key => $value) {
                $attributes['attribute'][] = array(
                    'description' => $key,
                    'value' => $value
                );
            }
        }
        return $attributes;
    }

    public function isEmptyArray($data)
    {
        if (is_array($data) && empty($data)) {
            return '';
        }
        return $data;
    }

    public function processResponseArray()
    {
        $helper = $this->getHelper();
        $cartHelper = $this->commonCartHelper;
        /* @var $cartHelper Epicor_Common_Helper_Cart */

        $response = $this->getResponse();
        $success = false;
        $qtyMismatch = array();
        #Mage::getSingleton('core/session')->setErpQuoteNumber($this->getResponse()->getOrder()->getQuoteNumber());
        if (!isset($response['order']['quoteNumber'])) {
            $quoteNumber = '';
        } else if (is_array($response['order']['quoteNumber'])) {
            $quoteNumber = isset($response['order']['quoteNumber'][0]) ? $response['order']['quoteNumber'][0] : '';
        } else {
            $quoteNumber = $response['order']['quoteNumber'];
        }
        if (!isset($response['order']['contractCode'])) {
            $eccContractCode = '';
        } elseif (is_array($response['order']['contractCode']) && !empty($response['order']['contractCode'])) {
            $eccContractCode = null;
        } elseif (empty($response['order']['contractCode'])) {
            $eccContractCode = '';
        } else {
            $eccContractCode = $response['order']['contractCode'];
        }

        $quote = false;
        $this->_quote->setEccBasketErpQuoteNumber($quoteNumber);
        if (!$this->isErrorStatusCode()) {
            if ($this->_quote instanceof \Magento\Sales\Model\Order || $this->_quote instanceof \Epicor\Comm\Model\Order) {
                $quote = $this->quoteQuoteFactory->create()->load($this->_quote->getQuoteId());
                $quote->setEccBasketErpQuoteNumber($this->_quote->getEccBasketErpQuoteNumber());
                $quote->save();
            }


            $store = $this->storeManager->getStore();
            /* @var $store Epicor_Comm_Model_Store */

            if ($this->getSaveChanges()) {
                $this->registry->register('bsv-processing', true);

                $shippingAddress = ($this->_quote->getIsMultiShipping()) ? $this->getShippingAddress() : $this->_quote->getShippingAddress();

                //  $bsv_quote = $this->getResponse();

                $lines = array();

                $linesGroup = $response['lines'];
                if ($linesGroup && isset($linesGroup['line'])) {
                    $lines = $linesGroup['line'];

                    if (!is_array($lines)) {
                        $lines = array($lines);
                    }
                }
                $lineData = array();
                if ($lines && !isset($lines[0])) {
                    $temp = $lines;
                    $lines = [];
                    $lines[0] = $temp;
                }

                foreach ($lines as $line) {
                    $lineData[(int)$line['@attributes']['number']] = $line;
                }

                if ($this->_quote->getIsMultiShipping()) {
                    $shippingAddress->unsetData('cached_items_all');
                    $shippingAddress->unsetData('cached_items_nominal');
                    $shippingAddress->unsetData('cached_items_nonnominal');
                    $items = $shippingAddress->getAllItems();
                } else {
                    $items = $this->_quote->getAllItems();
                }

                $lineNumber = 1;
                $bsvQtyNotifyEnabled = $this->scopeConfig->getValue('epicor_comm_enabled_messages/bsv_request/notify_qty_change', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                foreach ($items as $item) {
                    /* @var $item Mage_Sales_Model_Quote_Item */

                    if (!isset($lineData[$lineNumber]) && is_null($item->getParentItemId())) {
                        $item->delete();
                    } else if (isset($lineData[$lineNumber])) {

                        $line = $lineData[$lineNumber];

                        $uomArr = $helper->splitProductCode($item->getSku());
                        $productSku = $uomArr[0];
                        $uomCode = $uomArr[1];

                        if ($productSku != $line['productCode']) {
                            // throw an exception here
                        }

                        $price = $line['price'];

                        if (isset($line['discount']) && $line['discount']['value']) {
                            $discountValue = $line['discount']['value'];
                            $price += ($discountValue / $line['quantity']);
                        }

//                            echo '<pre>'; print_r($line); exit;
                        $item->setEccOriginalPrice($price);
                        if ($bsvQtyNotifyEnabled) {
                            $fromECC = floor($item->getQty() * 100) / 100;
                            $fromERP = floor($line['quantity'] * 100) / 100;
                            if ($fromECC != $fromERP) {
                                $qtyMismatch[$item->getId()] = $line['productCode'];
                            }
                        }

                        // note, if rounding is needed, use this->_quote->getStore()->roundPrice($price)
                        // previously setCustomPrice, setPrice and setRowTotal all had rounding on

                        $this->registry->unregister('QuantityValidatorObserver');
                        $this->registry->register('QuantityValidatorObserver', 1);
                        $decimalPlaces = $helper->getDecimalPlaces($item->getProduct());
                        $bsvqty = $helper->qtyRounding($line['quantity'], $decimalPlaces);
                        $item->setQty($bsvqty);
                        $this->registry->unregister('QuantityValidatorObserver');

                        $item->setOriginalCustomPrice($line['price']);
                        $item->setCustomPrice(null);
                        //$item->setPrice($line->getPrice());
                        //$item->setBasePrice($line->getPrice());
                        //$item->setRowTotal($item->getOriginalCustomPrice() * $line->getQuantity());
                        //$item->setBaseRowTotal($item->getOriginalCustomPrice() * $line->getQuantity());
                        //$item->setOriginalCustomPrice($line->getPrice());
                        //$item->setCustomPrice($line->getPrice());
                        $item->getProduct()->setIsSuperMode(true);
                        $locationCode = '';
                        if (isset($line['locationCode'])) {
                            $locationCode = $this->isEmptyArray($line['locationCode']);
                        }
                        $item->setEccLocationCode($locationCode);
                        $item->setEccBsvPrice((isset($line['price']) && is_numeric($line['price'])) ? $store->roundPrice($line['price'], 4) : null);
                        $item->setEccBsvPriceInc((isset($line['priceInc']) && is_numeric($line['priceInc'])) ? $store->roundPrice($line['priceInc'], 4) : null);
                        $item->setEccBsvLineValue((isset($line['lineValue']) && is_numeric($line['lineValue'])) ? $store->roundPrice($line['lineValue'], 4) : null);
                        $item->setEccBsvLineValueInc((isset($line['lineValueInc']) && is_numeric($line['lineValueInc'])) ? $store->roundPrice($line['lineValueInc'], 4) : null);
                        //$item->getProduct()->setIsSuperMode(true);
                        //$item->getProduct()->setPrice($line->getPrice());
                        //save delivery date
                        // if latest item on bsv has greater date than previously save it
                        if (isset($line['eccNextDeliveryDate']) && $this->_quote->getEccNextDeliveryDate() < $line['eccNextDeliveryDate']) {
                            $this->_quote->setEccNextDeliveryDate($line['eccNextDeliveryDate']);
                        }

                        // unset the linedata so that we know what has been processed
                        unset($lineData[$lineNumber]);
                    }
                    ++$lineNumber;
                }


                // any linedata left will need to be added
                if (!empty($lineData)) {
                    foreach ($lineData as $line) {
                        $uom = $line->getUnitOfMeasureCode();
                        $separator = $helper->getUOMSeparator();
                        $uomPartNo = $line->getProductCode() . $separator . $uom;

                        $productId = $this->catalogProductFactory->create()->getIdBySku($uomPartNo);
                        //product is not UOM product
                        if (!$productId) {
                            $productId = $this->catalogProductFactory->create()->getIdBySku($line->getProductCode());
                        }

                        $product = $this->catalogProductFactory->create()->load($productId);

                        if ($product->getId()) {

                            $options = array(
                                'qty' => $line->getQuantity(),
                                'location_code' => $line->getLocationCode(),
                                'bsv_values' => array(
                                    'price' => is_numeric($line->getPrice()) ? $store->roundPrice($line->getPrice(), 4) : null,
                                    'price_inc' => is_numeric($line->getPriceInc()) ? $store->roundPrice($line->getPriceInc(), 4) : null,
                                    'line_value' => is_numeric($line->getLineValue()) ? $store->roundPrice($line->getLineValue(), 4) : null,
                                    'line_value_inc' => is_numeric($line->getPriceInc()) ? $store->roundPrice($line->getLineValueInc(), 4) : null
                                )
                            );

                            $this->_quote->addLine($product, $options);

                            //save delivery date
                            if ($this->_quote->getEccNextDeliveryDate() < $line->getEccNextDeliveryDate()) {
                                $this->_quote->setEccNextDeliveryDate($line->getEccNextDeliveryDate());
                            }
                        }
                    }
                }
                //if ecc_is_dda_date not set, store bsv date in required date
                if (!$this->_quote->getEccIsDdaDate()) {
                    $this->_quote->setEccRequiredDate($this->_quote->getEccNextDeliveryDate() ? $this->_quote->getEccNextDeliveryDate() : "0000-00-00");
                }

                $orderDetails = $response['order'];

                $shippingAddress->setEccBsvGoodsTotal(isset($orderDetails['goodsTotal']) ? $store->roundPrice($orderDetails['goodsTotal'], 4) : null);
                $shippingAddress->setEccBsvGoodsTotalInc(isset($orderDetails['goodsTotalInc']) ? $store->roundPrice($orderDetails['goodsTotalInc'], 4) : null);

                $controller = $this->request->getControllerName();
                $action = $this->request->getActionName();

                $shippingAddress->setEccBsvCarriageAmount((isset($orderDetails['carriageAmount']) && is_numeric($orderDetails['carriageAmount'])) ? $store->roundPrice($orderDetails['carriageAmount'], 4) : null);
                $shippingAddress->setEccBsvCarriageAmountInc((isset($orderDetails['carriageAmountInc']) && is_numeric($orderDetails['carriageAmountInc'])) ? $store->roundPrice($orderDetails['carriageAmountInc'], 4) : null);

                $shippingAddress->setEccBsvGrandTotal((isset($orderDetails['grandTotal']) && is_numeric($orderDetails['grandTotal'])) ? $store->roundPrice($orderDetails['grandTotal'], 4) : null);
                $shippingAddress->setEccBsvGrandTotalInc((isset($orderDetails['grandTotalInc']) && is_numeric($orderDetails['grandTotalInc'])) ? $store->roundPrice($orderDetails['grandTotalInc'], 4) : null);

                $shippingAddress->unsetData('cached_items_all');
                $shippingAddress->unsetData('cached_items_nominal');
                $shippingAddress->unsetData('cached_items_nonnominal');
                $shippingAddress->getAllItems();

                /* @var $shippingAddress Epicor_Comm_Model_Quote_Address */

                /* WSO-6044 fix */
                if ($this->_quote->getIsMultiShipping()) {
                    $shippingAddress->removeAllShippingRates();
                    $shippingAddress->setCollectShippingRates(true);
                    $shippingAddress->collectShippingRates();
                }

                // only set cartWasUpdated to true if not on the checkout page
                // otherwise you get issues if you reload the cart page before finishing the order
                // checkout controller : onepage
                // verifone controller : payment
                // others might need to be added
                if ($this->request->getControllerName() == ('cart')) {
                    $this->checkoutSession->setCartWasUpdated(true);
                }
            }
            /* validate request and response qty mismatch when add product to cart */
            if ($qtyMismatch && $bsvQtyNotifyEnabled) {
                $this->checkoutSession->setBsvErpQtyMisMatch($qtyMismatch);
                $mismatchqty = $this->checkoutSession->getBsvErpQtyMisMatch();
                if ($mismatchqty) {
                    $this->_eventManager->dispatch('send_notify_qty_message', ['qty_message' => $mismatchqty]);
                }
            } else {
                $this->checkoutSession->unsBsvErpQtyMisMatch($qtyMismatch);
            }
            $success = true;
        } elseif (isset($response['order']) && $response['order'] && $quoteNumber) {
            $this->_quote->setEccBasketErpQuoteNumber($quoteNumber);
            if ($eccContractCode) {
                $this->_quote->setEccContractCode($eccContractCode);
            }
            if ($this->_quote instanceof \Magento\Sales\Model\Order || $quote instanceof \Epicor\Comm\Model\Order) {
                $quote = $this->quoteQuoteFactory->create()->load($this->_quote->getQuoteId());
                $quote->setEccBasketErpQuoteNumber($this->_quote->getEccBasketErpQuoteNumber());
                if ($this->_quote->getEccContractCode()) {
                    $quote->setEccContractCode($this->_quote->getEccContractCode());
                }
                $quote->save();
            }
            if ($this->request->getControllerName() == ('cart')) {
                $this->checkoutSession->setCartWasUpdated(true);
            }
        }
        $this->registry->unregister('bsv-processing');
        return $success;
    }


    public function processResponse()
    {
        $helper = $this->getHelper();
        $cartHelper = $this->commonCartHelper;
        /* @var $cartHelper Epicor_Common_Helper_Cart */

        //set non erp products values
        $nonErpProductOptions = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $nonErpProductEnabled = $this->scopeConfig->getValue('epicor_product_config/non_erp_products/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


        $success = false;
        $qtyMismatch = array();
        if (!$this->isErrorStatusCode()) {
            #Mage::getSingleton('core/session')->setErpQuoteNumber($this->getResponse()->getOrder()->getQuoteNumber());
            $this->_quote->setEccBasketErpQuoteNumber($this->getResponse()->getOrder()->getQuoteNumber());
            if ($this->_quote instanceof \Magento\Sales\Model\Order || $this->_quote instanceof \Epicor\Comm\Model\Order) {
                $quote = $this->quoteQuoteFactory->create()->load($this->_quote->getQuoteId());
                $quote->setEccBasketErpQuoteNumber($this->_quote->getEccBasketErpQuoteNumber());
                $quote->save();
            }


            $store = $this->storeManager->getStore();
            /* @var $store Epicor_Comm_Model_Store */

            if ($this->getSaveChanges()) {
                $this->registry->register('bsv-processing', true);

                $shippingAddress = ($this->_quote->getIsMultiShipping()) ? $this->getShippingAddress() : $this->_quote->getShippingAddress();

                $bsv_quote = $this->getResponse();

                $lines = array();

                $linesGroup = $bsv_quote->getLines();
                if ($linesGroup) {
                    $lines = $linesGroup->getLine();

                    if (!is_array($lines)) {
                        $lines = array($lines);
                    }
                }

                $lineData = array();

                foreach ($lines as $line) {
                    $lineData[(int)$line->getData('_attributes')->getNumber()] = $line;
                }

                if ($this->_quote->getIsMultiShipping()) {
                    $shippingAddress->unsetData('cached_items_all');
                    $shippingAddress->unsetData('cached_items_nominal');
                    $shippingAddress->unsetData('cached_items_nonnominal');
                    $items = $shippingAddress->getAllItems();
                } else {
                    $items = $this->_quote->getAllItems();
                }

                $lineNumber = 1;
                $bsvQtyNotifyEnabled = $this->scopeConfig->getValue('epicor_comm_enabled_messages/bsv_request/notify_qty_change', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                foreach ($items as $item) {
                    /* @var $item Mage_Sales_Model_Quote_Item */

                    if (!isset($lineData[$lineNumber])) {
                        $erpProduct = true;
                        if ($nonErpProductEnabled && $nonErpProductOptions = 'request') {

                            $erpProduct = $this->catalogResourceModelProductFactory->create()->getAttributeRawValue($item->getProductId(), 'sku_type', Mage::app()->getStore());
                        }

                        //erp product will only be true if not a non erp product
                        if ($erpProduct) {
                            $item->delete();
                        }
                    } else {

                        $line = $lineData[$lineNumber];

                        $uomArr = $helper->splitProductCode($item->getSku());
                        $productSku = $uomArr[0];
                        $uomCode = $uomArr[1];

                        if ($productSku != $line->getProductCode()) {
                            // throw an exception here
                        }

                        $price = $line->getPrice();

                        if ($line->getDiscount()) {
                            $discountValue = $line->getDiscount()->getValue();
                            $price += ($discountValue / $line->getQuantity());
                        }

                        $item->setEccOriginalPrice($price);
                        if ($bsvQtyNotifyEnabled) {
                            $fromECC = floor($item->getQty() * 100) / 100;
                            $fromERP = floor($line->getQuantity() * 100) / 100;
                            if ($fromECC != $fromERP) {
                                $qtyMismatch[$item->getId()] = $line->getProductCode();
                            }
                        }
                        // note, if rounding is needed, use this->_quote->getStore()->roundPrice($price)
                        // previously setCustomPrice, setPrice and setRowTotal all had rounding on

                        $item->setQty($line->getQuantity());
                        $item->setOriginalCustomPrice($line->getPrice());
                        $item->setCustomPrice(null);
                        //$item->setPrice($line->getPrice());
                        //$item->setBasePrice($line->getPrice());
                        //$item->setRowTotal($item->getOriginalCustomPrice() * $line->getQuantity());
                        //$item->setBaseRowTotal($item->getOriginalCustomPrice() * $line->getQuantity());
                        //$item->setOriginalCustomPrice($line->getPrice());
                        //$item->setCustomPrice($line->getPrice());
                        $item->getProduct()->setIsSuperMode(true);

                        $item->setEccLocationCode($line->getLocationCode());
                        $item->setEccBsvPrice(is_numeric($line->getPrice()) ? $store->roundPrice($line->getPrice(), 4) : null);
                        $item->setEccBsvPriceInc(is_numeric($line->getPriceInc()) ? $store->roundPrice($line->getPriceInc(), 4) : null);
                        $item->setEccBsvLineValue(is_numeric($line->getLineValue()) ? $store->roundPrice($line->getLineValue(), 4) : null);
                        $item->setEccBsvLineValueInc(is_numeric($line->getLineValueInc()) ? $store->roundPrice($line->getLineValueInc(), 4) : null);
                        //$item->getProduct()->setIsSuperMode(true);
                        //$item->getProduct()->setPrice($line->getPrice());
                        //save delivery date
                        // if latest item on bsv has greater date than previously save it
                        if ($this->_quote->getEccNextDeliveryDate() < $line->getEccNextDeliveryDate()) {
                            $this->_quote->setEccNextDeliveryDate($line->getEccNextDeliveryDate());
                        }

                        // unset the linedata so that we know what has been processed
                        unset($lineData[$lineNumber]);
                    }
                    ++$lineNumber;
                }


                // any linedata left will need to be added
                if (!empty($lineData)) {
                    foreach ($lineData as $line) {
                        $uom = $line->getUnitOfMeasureCode();
                        $separator = $helper->getUOMSeparator();
                        $uomPartNo = $line->getProductCode() . $separator . $uom;

                        $productId = $this->catalogProductFactory->create()->getIdBySku($uomPartNo);
                        //product is not UOM product
                        if (!$productId) {
                            $productId = $this->catalogProductFactory->create()->getIdBySku($line->getProductCode());
                        }

                        $product = $this->catalogProductFactory->create()->load($productId);

                        if ($product->getId()) {

                            $options = array(
                                'qty' => $line->getQuantity(),
                                'location_code' => $line->getLocationCode(),
                                'bsv_values' => array(
                                    'price' => is_numeric($line->getPrice()) ? $store->roundPrice($line->getPrice(), 4) : null,
                                    'price_inc' => is_numeric($line->getPriceInc()) ? $store->roundPrice($line->getPriceInc(), 4) : null,
                                    'line_value' => is_numeric($line->getLineValue()) ? $store->roundPrice($line->getLineValue(), 4) : null,
                                    'line_value_inc' => is_numeric($line->getPriceInc()) ? $store->roundPrice($line->getLineValueInc(), 4) : null
                                )
                            );

                            $this->_quote->addLine($product, $options);

                            //save delivery date
                            if ($this->_quote->getEccNextDeliveryDate() < $line->getEccNextDeliveryDate()) {
                                $this->_quote->setEccNextDeliveryDate($line->getEccNextDeliveryDate());
                            }
                        }
                    }
                }
                //if ecc_is_dda_date not set, store bsv date in required date
                if (!$this->_quote->getEccIsDdaDate()) {
                    $this->_quote->setEccRequiredDate($this->_quote->getEccNextDeliveryDate());
                }

                $orderDetails = $bsv_quote->getOrder();

                $shippingAddress->setEccBsvGoodsTotal($orderDetails->getGoodsTotal() ? $store->roundPrice($orderDetails->getGoodsTotal(), 4) : null);
                $shippingAddress->setEccBsvGoodsTotalInc($orderDetails->getGoodsTotalInc() ? $store->roundPrice($orderDetails->getGoodsTotalInc(), 4) : null);

                $controller = $this->request->getControllerName();
                $action = $this->request->getActionName();

                $shippingAddress->setEccBsvCarriageAmount(is_numeric($orderDetails->getCarriageAmount()) ? $store->roundPrice($orderDetails->getCarriageAmount(), 4) : null);
                $shippingAddress->setEccBsvCarriageAmountInc(is_numeric($orderDetails->getCarriageAmountInc()) ? $store->roundPrice($orderDetails->getCarriageAmountInc(), 4) : null);

                $shippingAddress->setEccBsvGrandTotal(is_numeric($orderDetails->getGrandTotal()) ? $store->roundPrice($orderDetails->getGrandTotal(), 4) : null);
                $shippingAddress->setEccBsvGrandTotalInc(is_numeric($orderDetails->getGrandTotalInc()) ? $store->roundPrice($orderDetails->getGrandTotalInc(), 4) : null);

                $shippingAddress->unsetData('cached_items_all');
                $shippingAddress->unsetData('cached_items_nominal');
                $shippingAddress->unsetData('cached_items_nonnominal');
                $shippingAddress->getAllItems();

                /* @var $shippingAddress Epicor_Comm_Model_Quote_Address */

                /* WSO-6044 fix */
                if ($this->_quote->getIsMultiShipping()) {
                    $shippingAddress->removeAllShippingRates();
                    $shippingAddress->setCollectShippingRates(true);
                    $shippingAddress->collectShippingRates();
                }

                // only set cartWasUpdated to true if not on the checkout page
                // otherwise you get issues if you reload the cart page before finishing the order
                // checkout controller : onepage
                // verifone controller : payment
                // others might need to be added
                if ($this->request->getControllerName() == ('cart')) {
                    $this->checkoutSession->setCartWasUpdated(true);
                }
            }
            /* validate request and response qty mismatch when add product to cart */
            if ($qtyMismatch && $bsvQtyNotifyEnabled) {
                $this->checkoutSession->setBsvErpQtyMisMatch($qtyMismatch);
                $mismatchqty = $this->checkoutSession->getBsvErpQtyMisMatch();
                if ($mismatchqty) {
                    $this->_eventManager->dispatch('send_notify_qty_message', ['qty_message' => $mismatchqty]);
                }
            } else {
                $this->checkoutSession->unsBsvErpQtyMisMatch($qtyMismatch);
            }
            $success = true;
        } elseif ($this->getResponse()->getOrder() && $this->getResponse()->getOrder()->getQuoteNumber()) {
            $this->_quote->setEccBasketErpQuoteNumber($this->getResponse()->getOrder()->getQuoteNumber());
            if ($this->getResponse()->getOrder()->getEccContractCode()) {
                $this->_quote->setEccContractCode($this->getResponse()->getOrder()->getEccContractCode());
            }
            if ($this->_quote instanceof \Magento\Sales\Model\Order || $quote instanceof \Epicor\Comm\Model\Order) {
                $quote = $this->quoteQuoteFactory->create()->load($this->_quote->getQuoteId());
                $quote->setEccBasketErpQuoteNumber($this->_quote->getEccBasketErpQuoteNumber());
                if ($this->_quote->getEccContractCode()) {
                    $quote->setEccContractCode($this->_quote->getEccContractCode());
                }
                $quote->save();
            }
            if ($this->request->getControllerName() == ('cart')) {
                $this->checkoutSession->setCartWasUpdated(true);
            }
        }
        $this->registry->unregister('bsv-processing');
        return $success;
    }

    /**
     * get User Nofification message on Error
     *
     * @return string
     */
    public function userNotificationMessage($error = true, $genericErrorTxt, $erpErrorTxt)
    {
        if ($this->strposa($this->request->getOriginalPathInfo(), ['shipping-information', 'payment-information', 'SaveBranchInformation']) !== false) {
            $errorText = $erpErrorTxt;
        } else {
            $errorText = parent::userNotificationMessage($error, $genericErrorTxt, $erpErrorTxt);
        }

        return $errorText;
    }

    /**
     * @param $quote
     */
    private function cleanEmptyQuote($quote)
    {
        $data = [
            'ecc_bsv_goods_total' => null,
            'ecc_bsv_goods_total_inc' => null,
            'ecc_bsv_carriage_amount' => null,
            'ecc_bsv_carriage_amount_inc' => null,
            'ecc_bsv_discount_amount' => null,
            'ecc_bsv_grand_total' => null,
            'ecc_bsv_grand_total_inc' => null,
            'base_subtotal' => 0,
            'subtotal' => 0,
            'base_subtotal_incl_tax' => 0,
            'base_subtotal_total_incl_tax' => 0,
            'subtotal_incl_tax' => 0,
            'base_shipping_amount' => 0,
            'shipping_amount' => 0,
            'base_shipping_incl_tax' => 0,
            'shipping_incl_tax' => 0,
            'base_grand_total' => 0,
            'grand_total' => 0
        ];

        $address = $quote->getShippingAddress();
        $address->setBaseTotalAmount('subtotal', 0);
        $address->setBaseTotalAmount('grand', 0);
        $address->setTotalAmount('subtotal', 0);
        $address->setTotalAmount('grand', 0);
        $address->addData($data);
        $quote->addData($data);
    }

}
