<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Message\Request;


/**
 * Request - DDA Delivery Dates Availability
 * Message used for requesting delivery dates from an erp.
 * 
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */

/**
 * @method void setQuote(Mage_Sales_Model_Quote $value)
 */
class Dda extends \Epicor\Comm\Model\Message\Request
{

    private $_dates;
    private $_defaultDate;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    protected $arpaymentsHelper;    

    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        $this->arpaymentsHelper = $arpaymentsHelper;
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->setMessageType('DDA');
        $this->setLicenseType(array('Consumer', 'Customer'));
        $this->setConfigBase('epicor_comm_enabled_messages/dda_request/');
        $this->_dates = array();
        $quote = $this->checkoutCart->getQuote();
        if ($quote->getEccRequiredDate() && $quote->getEccRequiredDate() != '0000-00-00') {
            $this->_dates[] = $quote->getEccRequiredDate();
        } else {
            $default_shipping_days = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/daystoship', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            //M1 > M2 Translation Begin (Rule 32)
            //$this->_dates[] = $this->getHelper()->getLocalDate(strtotime('+' . $default_shipping_days . ' days'), 'yyyy-MM-dd');
            $this->_dates[] = $this->getHelper()->getLocalDate(strtotime('+' . $default_shipping_days . ' days'), \IntlDateFormatter::MEDIUM);
            //M1 > M2 Translation End
        }

        $this->_defaultDate = $this->_dates[0];

    }


    /**
     * Create a DDA request
     *
     * @return boolean
     */
    public function buildRequest()
    {
        
        $arPaymentsPage = $this->arpaymentsHelper->checkArpaymentsPage();
        if($arPaymentsPage || $this->getQuote()->getArpaymentsQuote()) {
            return false;
        }  

        
        $fullAccountNumber = $this->getAccountNumber(true);
        $accountNumber = $this->getAccountNumber();

        $this->setMessageSecondarySubject($accountNumber);

        $method = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_shipping_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $helper = $this->getHelper();
        /* @var $helper Epicor_Comm_Helper_Messaging */

        $shippingAddress = $this->getQuote()->getShippingAddress();
        $shippingAddressCode = $helper->getErpAddress($this->getQuote()->getShippingAddress()->getCustomerAddressId(), $fullAccountNumber);

        $data = $this->getMessageTemplate();

        $data['messages']['request']['body'] = array_merge($data['messages']['request']['body'], array(
            'accountNumber' => $accountNumber,
            'delivery' => array(
                'deliveryAddress' => $helper->formatAddress($shippingAddress, 'shipping'),
//                'deliveryAddress' => array(
//                    'addressCode' => $shippingAddressCode->getEccErpAddressCode(),
//                    'contactName' => $helper->stripNonPrintableChars($shippingAddress->getName()),
//                    'name' => $helper->stripNonPrintableChars($shippingAddress->getCompany()),
//                    'address1' => $helper->stripNonPrintableChars($shippingAddress->getStreet1()),
//                    'address2' => $helper->stripNonPrintableChars($shippingAddress->getStreet2()),
//                    'address3' => $helper->stripNonPrintableChars($shippingAddress->getStreet3()),
//                    'city' => $helper->stripNonPrintableChars($shippingAddress->getCity()),
//                    'county' => $helper->stripNonPrintableChars($shippingAddress->getRegion()),
//                    'country' => $helper->getErpCountryCode($shippingAddress->getCountry_id()),
//                    'postcode' => $helper->stripNonPrintableChars($shippingAddress->getPostcode()),
//                    'telephoneNumber' => $helper->stripNonPrintableChars($shippingAddress->getTelephone()),
//                    'faxNumber' => $helper->stripNonPrintableChars($shippingAddress->getFax()),
//                ),
                'methodCode' => $method,
                'lines' => array()
            ),
        ));

        $basket = $this->checkoutSession;

        $items = $basket->getQuote()->getAllItems();
        foreach ($items as $item) {
            if (!$item->isDeleted() && ($item->getParentId() == null || $this->getPromotions())) {
                $uomArr = $helper->splitProductCode($item->getSku());
                $productSku = $uomArr[0];
                $uomCode = $uomArr[1];

                $quantity = $item->getQty() == null ? $item->getQtyOrdered() : $item->getQty();
                $data['messages']['request']['body']['delivery']['lines']['line'][] = array(
                    'productCode' => $productSku,
                    'locationCode' => $item->getEccLocationCode(),
                    'quantity' => $helper->qtyRounding($quantity)
                );
            }
        }

        $this->setOutXml($data);

        return true;
    }

    /**
     * Works out the status description based on the code
     * 
     * @param string $statusCode
     * 
     * @return string
     */
    function statusCodeDescription($statusCode)
    {
        if ($this->isSuccessfulStatusCode($statusCode)) {
            $description = 'DDA request been successful';
        } else {
            $description = 'An error has occurred while requesting available delivery dates';
        }
        return $description;
    }

    public function getDates()
    {
        return $this->_dates;
    }

    /**
     * Processes the DDA response
     * 
     * @return boolean
     */
    public function processResponse()
    {
        $this->_dates = array();
        $excludeStr = $this->scopeConfig->getValue('epicor_comm_enabled_messages/dda_request/excludedates', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $excludeDates = (explode("\r\n", html_entity_decode($excludeStr)));

        $helper = $this->commMessagingHelper;
        
        if(!$this->getResponse()->getDelivery()){
            $this->_dates = [];
            $this->setStatusCodeDescription($this->statusCodeDescription($this->getStatusCode()));
            return true;
        }
        // process dates
        if ($this->getResponse()->getDelivery()->getDates() &&
            $this->getResponse()->getDelivery()->getDates()->getDate()) {

            $dates = $this->getResponse()->getDelivery()->getDates()->getDate();

            if (!is_array($dates)) {
                $dates = array($dates);
            }

            foreach ($dates as $date) {
                //M1 > M2 Translation Begin (Rule 32)
                //$date = $this->getHelper()->getLocalDate($date, 'yyyy-MM-dd');
              //  $date = $this->getHelper()->getLocalDate($date, \IntlDateFormatter::MEDIUM);
                //M1 > M2 Translation End
                if (!in_array($date, $excludeDates))
                    $this->_dates[$date] = $date;
            }
        }

        // process rounds
        if ($this->getResponse()->getDelivery()->getRounds() &&
            $this->getResponse()->getDelivery()->getRounds()->getRound()) {

            $dates_per_round = $this->scopeConfig->getValue('epicor_comm_enabled_messages/dda_request/maxnumberofdatesperround', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $rounds = $this->getResponse()->getDelivery()->getRounds()->getRound();

            if (!is_array($rounds)) {
                $rounds = array($rounds);
            }

            foreach ($rounds as $round) {

                $day_of_week = $round->getDayOfWeek();
                $next_delivery = $round->getNextDeliveryDate();
                $freq = $round->getFrequency();

                if (empty($next_delivery)) {
                    if (is_numeric($day_of_week)) {
                        $day_of_week = $helper->getWeekDayName($day_of_week);
                    }
                    //M1 > M2 Translation Begin (Rule 32)
                    //$next_delivery = $this->getHelper()->getLocalDate(strtotime('next ' . $day_of_week), 'yyyy-MM-dd');
                    $next_delivery = $this->getHelper()->getLocalDate(strtotime('next ' . $day_of_week), \IntlDateFormatter::MEDIUM);
                    //M1 > M2 Translation End
                } else {
                    $this->getHelper()->getLocalDate(strtotime($next_delivery), \IntlDateFormatter::MEDIUM);
                    //$next_delivery = $this->getHelper()->getLocalDate(strtotime('next ' . $day_of_week), 'yyyy-MM-dd');
                    $next_delivery = $this->getHelper()->getLocalDate(strtotime('next ' . $day_of_week), \IntlDateFormatter::MEDIUM);
                    //M1 > M2 Translation End
                }

                if (!in_array($next_delivery, $excludeDates)) {
                    $this->_dates[$next_delivery] = $next_delivery;
                }

                $step = false;
                switch ($freq) {
                    case 'Daily':
                        $step = '1 day';
                        break;

                    case 'Weekly':
                        $step = '1 week';
                        break;

                    case 'Fortnightly':
                        $step = '2 weeks';
                        break;

                    default:
                        if (is_numeric($freq)) {
                            $step = $freq . ' week';
                            if ($freq > 1)
                                $step .= 's';
                        }
                        break;
                }

                if ($step) {
                    $i = 1;
                    while ($i < $dates_per_round) {
                        //M1 > M2 Translation Begin (Rule 32)
                        //$next_delivery = $this->getHelper()->getLocalDate(strtotime('+' . $step, strtotime($next_delivery)), 'yyyy-MM-dd');
                        $next_delivery = $this->getHelper()->getLocalDate(strtotime('+' . $step, strtotime($next_delivery)), \IntlDateFormatter::MEDIUM);
                        //M1 > M2 Translation End
                        if (!in_array($next_delivery, $excludeDates)) {
                            $this->_dates[$next_delivery] = $next_delivery;
                            $i++;
                        }
                    }
                }
            }
        }
        if (count($this->_dates) == 0) {
            $default_shipping_days = $this->scopeConfig->getValue('epicor_comm_enabled_messages/gor_request/daystoship', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->_dates[] = $this->_defaultDate;
        } else {

            sort($this->_dates);
            $limit = $this->scopeConfig->getValue('epicor_comm_enabled_messages/dda_request/maxavailabledates', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            // check if any dates less than BSV dates are to be included
            if ($this->scopeConfig->getValue('epicor_comm_enabled_messages/dda_request/no_dates_earlier_than_bsv', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                // remove any dates less than bsv date (next_delivery_date)
                $bsvDate = substr($this->getQuote()->getEccNextDeliveryDate(), 0, 10);  // strip out ccyy-mm-dd
                foreach ($this->_dates as $date) {
                    if ($date >= $bsvDate) {
                        $requiredDates[] = $date;
                    }
                }
                $this->_dates = $requiredDates;
            }
            if ($limit != 0)
                $this->_dates = array_slice($this->_dates, 0, $limit);
        }

        $this->setStatusCodeDescription($this->statusCodeDescription($this->getStatusCode()));
        return true;
    }

}
