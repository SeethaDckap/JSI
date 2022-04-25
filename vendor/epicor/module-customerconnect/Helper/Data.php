<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Helper;


class Data extends \Epicor\Comm\Helper\Messaging
{

    const SYNC_OPTION_ONLY_ERP = 0;
    const SYNC_OPTION_ONLY_ECC = 1;
    const SYNC_OPTION_ECC_ERP = 2;

    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Cuod
     */
    protected $customerconnectMessageRequestCuod;

    /**
     * @var \Epicor\Common\Helper\Locale\Format\Date
     */
    protected $commonLocaleFormatDateHelper;

    protected $cuodResponse;
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    public function __construct(
        \Epicor\Comm\Helper\Messaging\Context $context,
        \Epicor\Common\Helper\Locale\Format\Date $commonLocaleFormatDateHelper,
        \Epicor\Customerconnect\Model\Message\Request\Cuod $customerconnectMessageRequestCuod

    ) {
        $this->customerconnectMessageRequestCuod = $customerconnectMessageRequestCuod;
        $this->commonLocaleFormatDateHelper = $commonLocaleFormatDateHelper;
        parent::__construct($context);
        $this->localeResolver = $context->getLocaleResolver();
    }

    /**
     * Sends a CUOD request message
     *
     * @param string $erpAccountNumber
     * @param string $orderNumber
     * @param string $languageCode
     *
     * @return array
     */
    public function sendOrderRequest($erpAccountNumber, $orderNumber, $languageCode)
    {
        $cuod = $this->customerconnectMessageRequestCuod;
        $messageTypeCheck = $cuod->getHelper()->getMessageType('CUOD');

        $order = false;
        $error = '';

        if ($cuod->isActive() && $messageTypeCheck) {

            $cuod->setAccountNumber($erpAccountNumber)
                ->setOrderNumber($orderNumber)
                ->setLanguageCode($languageCode);

            if ($cuod->sendMessage()) {
                $order = $cuod->getResults();
            } else {
                $error = __('Failed to retrieve Order Details');
            }
        } else {
            $error = __('ERROR - Order Details not available');
        }

        return array(
            'order' => $order,
            'error' => $error
        );
    }

    public function urlWithoutHttp()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $baseUrlArray = array();
        switch (true) {
            case (stristr($baseUrl, 'http://') !== false):
                $baseUrlArray = explode("http://", $baseUrl);
                break;
            case (stristr($baseUrl, 'https://') !== false):
                $baseUrlArray = explode("https://", $baseUrl);
                break;
            default:
                $baseUrlArray[1] = $baseUrl;
                break;
        }
        return $baseUrlArray[1];
    }

    /**
     * Returns an invoice reorder URL fro the invoice object provided,
     *
     * Also optional to change the return url
     *
     * @param \Epicor\Comm\Model\Xmlvarien $invoiceObj
     * @param string $return
     * @return type
     */
    public function getInvoiceReorderUrl($invoiceObj, $return = '/customerconnect/invoices/')
    {
        $invoiceDetails = $this->encryptor->encrypt($this->getErpAccountNumber() . ']:[' . $invoiceObj->getInvoiceNumber());

        $params = array(
            'invoice' => $this->urlEncoder->encode($invoiceDetails),
            'attribute_type' => $invoiceObj->get_attributesType() ?: '',
            'return' => $this->urlEncoder->encode($return)
        );

        //M1 > M2 Translation Begin (Rule p2-4)
        //return Mage::getUrl('customerconnect/invoices/reorder', $params);
        return $this->_getUrl('customerconnect/invoices/reorder', $params);
        //M1 > M2 Translation End
    }

    /**
     * Returns an invoice reorder URL fro the invoice object provided,
     *
     * Also optional to change the return url
     *
     * @param \Epicor\Comm\Model\Xmlvarien $shipmentObj
     * @param string $return
     * @return type
     */
    public function getShipmentReorderUrl($shipmentObj, $return = '/customerconnect/shipments/')
    {

        $shipDetails = $this->encryptor->encrypt(
            $this->getErpAccountNumber()
            . ']:['
            . $shipmentObj->getPackingSlip()
            . ']:['
            . $shipmentObj->getOrderNumber()
        );

        $params = array(
            'shipment' => $this->urlEncoder->encode($shipDetails),
            'return' => $this->urlEncoder->encode($return)
        );

        //M1 > M2 Translation Begin (Rule p2-4)
        //return Mage::getUrl('customerconnect/shipments/reorder', $params);
        return $this->_getUrl('customerconnect/shipments/reorder', $params);
        //M1 > M2 Translation End
    }

    /**
     * Returns an order reorder URL fro the invoice object provided,
     *
     * Also optional to change the return url
     *
     * @param \Epicor\Comm\Model\Xmlvarien $orderObj
     * @param string $return
     * @return type
     */
    public function getOrderReorderUrl($orderObj, $return = '/customerconnect/dashboard/')
    {
        $orderDetails = $this->encryptor->encrypt($this->getErpAccountNumber() . ']:[' . $orderObj->getOrderNumber());

        $params = array(
            'order' => $this->urlEncoder->encode($orderDetails),
            'return' => $this->urlEncoder->encode($return)
        );

        //M1 > M2 Translation Begin (Rule p2-4)
        //return Mage::getUrl('customerconnect/orders/reorder', $params);
        return $this->_getUrl('customerconnect/orders/reorder', $params);
        //M1 > M2 Translation End
    }

    /**
     * Converts a date / timestamp to the format specified, using magento locale dates
     *
     * @param string $timestamp
     * @param string $format
     *
     * @return string
     */
    public function getLocalDate($timestamp, $format = \IntlDateFormatter::MEDIUM, $showTime = false)
    {
        $helper = $this->commonLocaleFormatDateHelper;
        return $helper->getLocalFormatDate($timestamp, $format, $showTime);
    }

    public function getEncodeOrderDetailsUrl($erpAccountNumber, $rowId)
    {
        $invoice = $this->urlEncoder->encode($this->encryptor->encrypt($erpAccountNumber . ']:[' . $rowId));
        return $invoice;
    }

    public function getCurrencyCode()
    {
        $currentCurrency = $this->storeManager->getStore()->getCurrentCurrencyCode();
        return $currentCurrency;
    }

    /*
     * Retrieves the sorder order for ewa quote attributes
     */

    public function sortQuoteEwaAttributes()
    {
        $newOptionsOrder = $this->scopeConfig->getValue('Epicor_Comm/ewa_options/quote_display_fields',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $newoptionsOrder = $newOptionsOrder ? unserialize($newOptionsOrder) : null;
        $optionsSelected = array_flip(explode(',', $this->scopeConfig->getValue('Epicor_Comm/ewa_options/ewa_display',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)));
        $newOptionByTypeOrder = array();
        if (in_array('base_description', array_keys($optionsSelected))) {
            $newOptionByTypeOrder['base_description'] = 'base_description';
        }
        foreach ($newoptionsOrder as $key => $option) {
            if ($option['ewaquotesortorder']) {
                $newOptionByTypeOrder[$option['ewaquotesortorder']] = $option['ewaquotesortorder'];
            }
        }
        $properOrderedArray = array_merge(array_flip(array_keys($newOptionByTypeOrder)), $optionsSelected);
        $splitKeys = array_keys($properOrderedArray);
        $requiredOptionsInSortOrder = array_combine($splitKeys, $splitKeys);
        return $requiredOptionsInSortOrder;
    }

    public static function convertPhpToIsoFormat($format)
    {
        if ($format === null) {
            return null;
        }

        $convert = array(
            'EEEE' => 'DD',
            'EEE' => 'D',
            'EE' => 'D',
            'E' => 'D',
            'D' => 'o',
            'dd' => 'd',
            'MMMM' => 'm',
            'MMM' => 'm',
            'MM' => 'm',
            'M' => 'm',
            'yyyy' => 'Y',
            'y' => 'Y',
            'Y' => 'Y',
            'yy' => 'Y' // Always long year format on frontend
        );

        $format = preg_replace('{(.)\1+}', '$1', $format);
        $escaped = false;
        $inEscapedString = false;
        $converted = array();
        foreach (str_split($format) as $char) {
            if (!$escaped && $char == '\\') {
                // Next char will be escaped: let's remember it
                $escaped = true;
            } elseif ($escaped) {
                if (!$inEscapedString) {
                    // First escaped string: start the quoted chunk
                    $converted[] = "'";
                    $inEscapedString = true;
                }
                // chunk, let's simply add $char as it is
                $converted[] = $char;
                $escaped = false;
            } elseif ($char == "'") {
                // Single quotes need to be escaped like this
                $converted[] = "''";
            } else {
                if ($inEscapedString) {
                    // Close the single-quoted chunk
                    $converted[] = "'";
                    $inEscapedString = false;
                }
                // Convert the unescaped char if needed
                if (isset($convert[$char])) {
                    $converted[] = $convert[$char];
                } else {
                    $converted[] = $char;
                }
            }
        }

        return implode($converted);
    }

    public function showMiscCharges()
    {
        $showMiscCharges = $this->getScopeConfig()->getValue('customerconnect_enabled_messages/crq_options/allow_misc_charges',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $hidePrices = $this->getEccHidePrice();
        $hidePrices = $hidePrices && in_array($hidePrices, [1, 2, 3]);
        $showMiscCharges = $showMiscCharges && !$hidePrices;
        return $showMiscCharges;
    }

    public function checkCusMiscView()
    {
        $showMiscCharges = $this->showMiscCharges();
        if (!$showMiscCharges) {
            return "0";
        }
        $customer = $this->getCustomer();
        $checkCustomer = $customer->getEccMiscViewType();
        if ($checkCustomer == "2" || (empty($checkCustomer) && $checkCustomer !== "0")) {
            $checkGlobal = $this->checkErpMiscView();
        } else {
            $checkGlobal = $checkCustomer;
        }
        if ($checkGlobal == "0") {
            $checkGlobal = "0";
        }
        return $checkGlobal;
    }

    /**
     * Checks  ERP level Toggle is enabled or not
     * @return boolean
     */
    public function checkErpMiscView()
    {
        $commHelper = $this->commHelper;
        /* @var $helper Epicor_Comm_Helper_Data */
        $erpAccount = $commHelper->getErpAccountInfo();
        /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
        $checkErp = $erpAccount->getMiscViewType();
        if ($checkErp == "2") {
            $checkGlobal = $this->checkGlobalMiscView();
        } else {
            $checkGlobal = $checkErp;
        }
        return $checkGlobal;
    }

    /**
     * Checks  Global Toggle Allowed or Not
     * @return boolean
     */
    public function checkGlobalMiscView()
    {
        $storeMiscView = $this->scopeConfig->getValue('customerconnect_enabled_messages/crq_options/view_misc_charges',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->storeManager->getStore());
        return $storeMiscView;
    }

    /**
     * @param $fileName
     * @return string
     */
    public function getFileNameToDisplay($fileName)
    {
        $filenameToDisplay = $fileName;
        $fileNameArray = explode("\\", $fileName);
        if (is_array($fileNameArray) && count($fileNameArray) > 0) {
            $filenameToDisplay = end($fileNameArray);
        }
        return $filenameToDisplay;
    }

    /**
     * Returns a recentpurchases reorder url from the recentpurchases object provided,
     *
     * Also optional to change the return url
     *
     * @param \Epicor\Comm\Model\Xmlvarien $recentpurchasesObj
     * @param string $return
     * @return type
     */
    public function getRecentpurchasesReorderUrl($recentpurchasesObj, $return = 'customerconnect/recentpurchases/')
    {
        $recentpurchaseDetails = json_encode($recentpurchasesObj->getData());
        $params = array(
            'recentpurchaseitem' => $this->urlEncoder->encode($recentpurchaseDetails),
            'return' => $this->urlEncoder->encode($return)
        );

        return $this->_getUrl('customerconnect/recentpurchases/reorder', $params);
    }

}