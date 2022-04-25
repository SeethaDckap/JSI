<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CRQU - Customer Rfq Update  
 * 
 * @method setRfqNumber()
 * @method getRfqNumber()
 * @method getNewData()
 * @method setNewData()
 * @method setOldData()
 * @method getOldData()
 * 
 */
class Crqu extends \Epicor\Customerconnect\Model\Message\Request
{

    private $_lineNumber = 1;
    private $_attachmentNumber = 0;
    private $_subData = array();
    private $_updateType = 'full';
    private $_isCustomerSalesRep = false;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory; 
    
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->request = $context->getRequest();
        parent::__construct(
            $context,
            $customerconnectMessagingHelper,
            $localeResolver,
            $resource,
            $resourceCollection,
            $data
        );
        $this->setMessageType('CRQU');
        $this->setConfigBase('customerconnect_enabled_messages/CRQU_request/');
        $this->setResultsPath('quote');
        $this->_isCustomerSalesRep = $this->commHelper->getCustomer()->isSalesRep();
        $this->_salesRepId = $this->commHelper->getCustomer()->getEccSalesRepId();
    }

    public function buildRequest()
    {
         $this->_lineNumber = 1;
         $this->_attachmentNumber = 0;
        //   don't proceed if account number not valid 
        if ($this->getAccountNumber()) {

            $message = $this->getMessageTemplate();

            $new = $this->getNewData();
            $old = $this->getOldData();

            $message['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
            $message['messages']['request']['body']['quoteSequence'] = $this->getQuoteSequence();
            $message['messages']['request']['body']['languageCode'] = $this->getLanguageCode();

            if (isset($new['delivery_method'])) {
                if ($new['delivery_method'] == 'other') {
                    $deliveryMethod = $new['delivery_method_other'];
                } else {
                    $deliveryMethod = $this->getHelper()->getShippingMethodMapping(
                        $new['delivery_method'], \Epicor\Comm\Helper\Messaging::MAGENTO_TO_ERP, false
                    );
                }
            } else {
                $deliveryMethod = '';
            }
            $priceChange = $this->_isCustomerSalesRep ? 'Y' : 'N';

            $baseCurrency = $this->storeManager->getStore()->getBaseCurrencyCode();
            $currencyCode = isset($old['currency_code']) ? $old['currency_code'] : $baseCurrency;
            $mHelper = $this->commMessagingHelper->create();
            $currency = $mHelper->getCurrencyMapping($currencyCode, \Epicor\Comm\Helper\Messaging::MAGENTO_TO_ERP);
            $module = $this->request->getModuleName();
            $controller = $this->request->getControllerName();
            $lines = $this->_processSubData('lines', $new);
            $oldCarriageAmt = isset($old['carriage_amount']) ? $old['carriage_amount'] : '';
            if ($module === "dealerconnect" && $controller === "quotes") {
                $dealerSubtotal = isset($new['dealer-subtotal']) ? $new['dealer-subtotal'] : $new['subtotal'];
                if ($dealerSubtotal == 'undefined') {
                    $dealerSubtotal = $this->getDealerSubtotal($lines);
                }
                $dealer = array(
                    'goodsTotal' => $dealerSubtotal,
                    'goodsTotalInc' => $dealerSubtotal,
                    'discountAmount' => isset($old['dealer']['discount_amount']) ? $old['dealer']['discount_amount'] : '',
                    'carriageAmount' => isset($new['shipping']) ? $new['shipping'] : (isset($new['dealer-shipping']) ? $new['dealer-shipping'] : 0),
                    'carriageAmountInc' => isset($new['shipping']) ? $new['shipping'] : (isset($new['dealer-shipping']) ? $new['dealer-shipping'] : 0),
                    'grandTotal' => $dealerSubtotal,
                    'grandTotalInc' => $dealerSubtotal,
                );
            } else {
                $dealer = array();
            }
            $rfq = array(
                '_attributes' => array(
                    'action' => $this->getAction(),
                    'processPricing' => $priceChange,
                    'dealer' => ($module === "dealerconnect" && ($controller === "quotes" || $controller === "claims")) ? "Y" : "N"
                ),
                'oldQuote' => array(
                    'requiredDate' => isset($old['required_date']) ? $old['required_date'] : '',
                    'customerReference' => isset($old['customer_reference']) ? $old['customer_reference'] : '',
                    'deliveryMethod' => isset($old['delivery_method']) ? $old['delivery_method'] : '',
                    'paymentTerms' => isset($old['payment_terms']) ? $old['payment_terms'] : '',
                    'noteText' => isset($old['note_text']) ? $old['note_text'] : '',
                ),
                'quoteNumber' => isset($old['quote_number']) ? $old['quote_number'] : '',
                'webReference' => isset($new['web_reference']) ? $new['web_reference'] : '',
                'quoteDate' => isset($old['quote_date']) ? $old['quote_date'] : '',
                'requiredDate' => $this->getHelper()->getFormattedInputDate($new['required_date'], self::DATE_FORMAT),
                'customerReference' => $new['customer_reference'],
                'salesReps' => array(),
                'salesRepId' => $this->_isCustomerSalesRep ? $this->_salesRepId : '',
                'contacts' => array(),
                'deliveryMethod' => $deliveryMethod,
                'paymentTerms' => isset($old['payment_terms']) ? $old['payment_terms'] : '',
                'fob' => isset($old['fob']) ? $old['fob'] : '',
                'taxid' => isset($old['taxid']) ? $old['taxid'] : '',
                'currencyCode' => $currency,
                'goodsTotal' => isset($old['goods_total']) ? $old['goods_total'] : '',
                'discount' => array(
                    'value' => isset($old['discount']['value']) ? $old['discount']['value'] : '',
                    'description' => isset($old['discount']['description']) ? $old['discount']['description'] : '',
                    'percent' => isset($old['discount']['percent']) ? $old['discount']['percent'] : '',
                ),
                'carriageAmount' => $oldCarriageAmt,
                'taxAmount' => isset($old['tax_amount']) ? $old['tax_amount'] : '',
                'grandTotal' => isset($old['grand_total']) ? $old['grand_total'] : '',
                'quoteStatus' => isset($old['quote_status']) ? $old['quote_status'] : '',
                'quoteEntered' => isset($old['quote_entered']) ? $old['quote_entered'] : '',
                'noteText' => $new['note_text'],
                'dealer' => $dealer,
                'quoteAddress' => $this->_getAddress('quote', $new, $old),
                'deliveryAddress' => $this->_getAddress('delivery', $new, $old),
                'lines' => array(),
                'attachments' => array(),
            );

            if ($lines) {
                $rfq['lines'] = array('line' => $lines);
            }

            $contacts = $this->_processSubData('contacts', $new);
            if ($contacts) {
                $rfq['contacts'] = array('contact' => $contacts);
            }

            $salesreps = $this->_processSubData('salesreps', $new);
            if ($salesreps) {
                $rfq['salesReps'] = array('salesRep' => $salesreps);
            }

            $attachments = $this->_processSubData('attachments', $new);
            if ($attachments) {
                $rfq['attachments'] = array('attachment' => $attachments);
            }

            $message['messages']['request']['body']['quote'] = $rfq;
            $this->setOutXml($message);
            return true;
        } else {
            return 'Missing account number';
        }
    }

    /**
     * Gets addres data form the odl & new data
     * 
     * @param string $type
     * @param array $newData
     * @param array $oldData
     * 
     * @return array
     */
    private function _getAddress($type, $newData, $oldData)
    {
        if (isset($oldData[$type . '_address'])) {
            $address = array(
                'old' . ucfirst($type) . 'Address' => $this->_getAddressData($oldData[$type . '_address'], $type),
            );
        } else {
            $address = array(
                'old' . ucfirst($type) . 'Address' => array(),
            );
        }

        return array_merge($address, $this->_getAddressData($newData[$type . '_address'], $type));
    }

    /**
     * Formats address data for usage in the message
     * 
     * @param array $data
     * 
     * @return array
     */
    private function _getAddressData($data, $type)
    {
        $helper = $this->customerconnectHelper;
        /* @var $helper Epicor_Customerconnect_Helper_Data */
        if (isset($data['county_id'])) {
            $region = $this->directoryRegionFactory->create()->load($data['county_id']);
            /* @var $region Mage_Directory_Model_Region */
            $data['county'] = $helper->getRegionNameOrCode($region->getCountryId(), $region->getCode());
        }

        $defaultAddCode = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_address_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $addCode = (
            isset($data['address_code'])
            && (!empty($data['address_code']) || $data['address_code'] === 0 || $data['address_code'] === '0')
        ) ? $data['address_code'] : $defaultAddCode;
        $name = isset($data['company']) && !empty($data['company']) ? $data['company'] : $data['name'];

        $addressData = array(
            'addressCode' => $addCode,
            'name' => $name,
            'address1' => isset($data['address1']) ? $data['address1'] : '',
            'address2' => isset($data['address2']) ? $data['address2'] : '',
            'address3' => isset($data['address3']) ? @$data['address3'] : '',
            'city' => $data['city'],
            'county' => $data['county'],
            'country' => $helper->getCountryCodeMapping($data['country']),
            'postcode' => $data['postcode'],
            'telephoneNumber' => @$data['telephone'],
            'mobileNumber' => @$data['mobile_number'],
            'faxNumber' => @$data['fax'],
            'emailAddress' => @$data['email'],
        );

        if ($type == 'delivery') {
            $carriage = '';

            if (isset($data['instructions'])) {
                $carriage = $data['instructions'];
            } else if (isset($data['carriageText'])) {
                $carriage = $data['carriageText'];
            } else if (isset($data['carriage_text'])) {
                $carriage = $data['carriage_text'];
            }

            $addressData['carriageText'] = $carriage;
        }

        return $addressData;
    }

    /**
     * Processes post data for  Lines / Attachments / Contacts / Sales Reps
     * into arrays for the xml
     * 
     * @param string $key - post data array key to look at
     * @param array $data - posted data
     * 
     * @return array
     */
    private function _processSubData($key, $data)
    {
        $this->_subData[$key] = array();
        if (isset($data[$key])) {
            if (isset($data[$key]['existing'])) {
                foreach ($data[$key]['existing'] as $rowIndex => $rowData) {
                    $oldData = unserialize(base64_decode($rowData['old_data']));
                    unset($rowData['old_data']);
                    if (isset($rowData['delete']) && in_array($rowData['delete'], array(1, 'on'))) {
                        $this->addSubDataArray($rowIndex, $key, 'R', $oldData, $oldData);
                    } else {
//                        if (method_exists($this, $key . 'Compare')) {
//                            $function = $key . 'Compare';
//                            if ($this->$function($rowIndex, $oldData, $rowData)) {
//                                $this->addSubDataArray($rowIndex, $key, 'U', $rowData, $oldData);
//                            }
//                        } else if ($rowData != $oldData) {
//                            $this->addSubDataArray($rowIndex, $key, 'U', $rowData, $oldData);
//                        } else {
                        // possibly set a different flag here: TBC 
                        $this->addSubDataArray($rowIndex, $key, 'U', $rowData, $oldData);
//                        }
                    }
                }
            }
            
            if (isset($data[$key]['new'])) {
                foreach ($data[$key]['new'] as $rowIndex => $rowData) {
                    $combiValue = '';
                    foreach ($rowData as $rowKey => $rowVal) {
                        if (!is_object($rowVal)) {
                            $combiValue .= $rowVal;
                        }
                    }

                    if (!empty($combiValue)) {
                        $this->addSubDataArray($rowIndex, $key, 'A', $rowData);
                    }
                }
            }
        }

        return $this->_subData[$key];
    }

    /**
     * Gets an individual row of data for the message
     * For Price breaks / Supplier UOM / Cross reference parts repeating groups
     * 
     * @param string $type - type of array to build
     * @param string $action - action type for the action attribute
     * @param array $newData - new data for the array
     * @param array $oldData - old data for the array
     * 
     */
    private function addSubDataArray($rowIndex, $type, $action, $newData, $oldData = null)
    {
       switch ($type) {
            case 'contacts' : $this->addContactArray($rowIndex, $action, $newData, $oldData);
                break;
            case 'salesreps' : $this->addSalesRepArray($rowIndex, $action, $newData, $oldData);
                break;
            case 'lines': $this->addLineArray($rowIndex, $action, $newData, $oldData);
                break;
            case 'attachments': $this->addAttachmentArray($rowIndex, $action, $newData, $oldData);
                break;
        }
    }

    /**
     * builds a price break array
     * 
     * @param string $action - action type for the action attribute
     * @param array $newData - new data for the array
     * @param array $oldData - old data for the array
     * 
     * @return type
     */
    private function addContactArray($rowIndex, $action, $newData, $oldData = null)
    {

        if ($action == 'A') {
            if (isset($newData['details'])) {
                $newData = unserialize(base64_decode($newData['details']));
            }
        }

        $contact = array(
            '_attributes' => array(
                'action' => $action
            ),
            'oldContact' => array(),
            'number' => $newData['number'],
            'name' => $newData['name'],
        );

        if (!empty($oldData)) {
            $contact['oldContact'] = array(
                'number' => $oldData['number'],
                'name' => $oldData['name'],
            );
        }

        $this->_subData['contacts'][] = $contact;
    }

    /**
     * builds a Supplier UOM array
     * 
     * @param string $action - action type for the action attribute
     * @param array $newData - new data for the array
     * @param array $oldData - old data for the array
     * 
     * @return type
     */
    private function addSalesRepArray($rowIndex, $action, $newData, $oldData = null)
    {
        $newData = $oldData;

        $salesRep = array(
            '_attributes' => array(
                'action' => $action
            ),
            'number' => $newData['number'],
            'name' => $newData['name'],
        );

        if (!empty($oldData)) {
            $salesRep['oldSalesRep'] = array(
                'number' => $oldData['number'],
                'name' => $oldData['name'],
            );
        }

        $this->_subData['salesreps'][] = $salesRep;
    }

    /**
     * builds a Supplier UOM array
     * 
     * @param string $action - action type for the action attribute
     * @param array $newData - new data for the array
     * @param array $oldData - old data for the array
     * 
     * @return type
     */
    private function addAttachmentArray($rowIndex, $action, $newData, $oldData = null)
    {
      
        $erpFileId = isset($newData['erp_file_id']) ? $newData['erp_file_id'] :
            (isset($oldData['erp_file_id']) ? $oldData['erp_file_id'] : '');
        $webFileId = isset($newData['web_file_id']) ? $newData['web_file_id'] :
            (isset($oldData['web_file_id']) ? $oldData['web_file_id'] : '');
        $desc = isset($newData['description']) ? $newData['description'] :
            (isset($oldData['description']) ? $oldData['description'] : '');
        $filename = isset($newData['filename']) ? $newData['filename'] :
            (isset($oldData['filename']) ? $oldData['filename'] : '');
        $url = isset($newData['url']) ? $newData['url'] :
            (isset($oldData['url']) ? $oldData['url'] : '');
        $status = isset($newData['attachment_status']) ? $newData['attachment_status'] :
            (isset($oldData['attachment_status']) ? $oldData['attachment_status'] : '');

        $number = isset($oldData['attachment_number']) ? $oldData['attachment_number'] : $this->_attachmentNumber;

        $this->_attachmentNumber = ($number >= $this->_attachmentNumber) ? $number + 1 : $this->_attachmentNumber;

        $attachment = array(
            '_attributes' => array(
                'action' => $action
            ),
            'attachmentNumber' => $number,
            'erpFileId' => $erpFileId,
            'webFileId' => $webFileId,
            'description' => $desc,
            'filename' => $filename,
            'url' => $url,
            'attachmentStatus' => $status,
        );

        if (!empty($oldData)) {
            $attachment['oldAttachment'] = array(
                'attachmentNumber' => $oldData['attachment_number'],
                'erpFileId' => $oldData['erp_file_id'],
                'webFileId' => $oldData['web_file_id'],
                'description' => $oldData['description'],
                'filename' => $oldData['filename'],
                'url' => $oldData['url'],
                'attachmentStatus' => $oldData['attachment_status'],
            );
        }

        $this->_subData['attachments'][] = $attachment;
    }

    /**
     * checks to see if a line has changed
     * 
     * @param array $oldData - old data for the array
     * @param array $newData - new data for the array
     * 
     * @return type
     */
    private function linesCompare($rowIndex, $oldData, $newData)
    {
        $changes = false;
        if (
            $oldData ['quantity'] != $newData['quantity'] || $oldData ['description'] != $newData['description'] || $oldData ['additionalText'] != $newData['additionalText'] || $oldData['requestDate'] != $newData['requestDate']
        ) {
            $changes = true;
        }

        $attachments = $this->getLineAttachments($rowIndex);
        // add compare attachments here

        if (isset($attachments['new'])) {
            $changes = true;
        } else {
            if (isset($attachments['existing'])) {
                foreach ($attachments['existing'] as $x => $attachment) {
                    $oldData = unserialize(base64_decode($attachment['old_data']));
                    unset($attachment['old_data']);
                    if ($this->attachmentsCompare($x, $oldData, $attachment)) {
                        $changes = true;
                    }
                }
            }
        }

        return $changes;
    }

    /**
     * checks to see if a line has changed
     * 
     * @param array $oldData - old data for the array
     * @param array $newData - new data for the array
     * 
     * @return type
     */
    private function attachmentsCompare($rowIndex, $oldData, $newData)
    {
        $changes = false;
        if (
            $oldData ['web_file_id'] != $newData['web_file_id'] || $oldData ['description'] != $newData['description'] || $oldData ['filename'] != $newData['filename']
        ) {
            $changes = true;
        }

        return $changes;
    }

    /**
     * builds a cross reference part array
     * 
     * @param string $action - action type for the action attribute
     * @param array $newData - new data for the array
     * @param array $oldData - old data for the array
     * 
     * @return type
     */
    private function addLineArray($rowIndex, $action, $newData, $oldData = null)
    {
        $type = '';
        $number = '';
        $productCodeType = '';
        $eccReturnType = '';
        
        if ($action == 'A' && (empty($newData['product_code']) || !isset($newData['product_code']))) {
            return;
        }

        if (!empty($oldData)) {
            $type = $oldData['_attributes']['type'];
            $number = $oldData['_attributes']['number'];
            if (!empty($oldData['product_code']['_attributes'])) {
                $productCodeType = $oldData['product_code']['_attributes']['type'];
            }
            if (isset($newData['ecc_return_type']) && isset($oldData['_attributes']['return_type']) && ($newData['ecc_return_type'] != $oldData['_attributes']['return_type'])) {
                $eccReturnType =  $newData['ecc_return_type'];
            } else if (isset($oldData['_attributes']['return_type'])) {
                $eccReturnType = $oldData['_attributes']['return_type'];
            }
            $this->_lineNumber = ($number > $this->_lineNumber) ? $number + 1 : $this->_lineNumber;
        } else {
            $number = $this->_lineNumber;
            $this->_lineNumber++;
            $type = 'N';
            $productCodeType = $newData['type'];
            $eccReturnType = isset($newData['ecc_return_type']) ? $newData['ecc_return_type'] : '';
        }
        if (!isset($newData['price']) && !isset($oldData['price'])) {
            $newData['price'] = 'TBC';
        }
        $price = ($action == 'A' || ($this->_isCustomerSalesRep && isset($newData['price']))) ? $newData['price'] : $oldData['price'];
        if(isset($newData['dealer_price_inc']) && $price != 'TBC'){
            $lineValue = $price * $newData['quantity'];
        }else{
            $newLineVal =  isset($newData['line_value']) ? $newData['line_value'] : (isset($newData['misc_line_total']) ? $newData['misc_line_total'] : (isset($oldData['misc_line_total']) ? $oldData['misc_line_total']: 'NA'));
            $lineValue = ($action == 'A' || ($this->_isCustomerSalesRep && $newLineVal !== 'NA')) ? $newLineVal : $oldData['line_value'];
        }
        $line = array(
            '_attributes' => array(
                'type' => $type,
                'number' => $number,
                'action' => $action
            ),
            'oldLine' => array(),
            'productCode' => array(
                '_attributes' => array(
                    'type' => $productCodeType
                ),
                ($action == 'A') ? $newData['product_code'] : $oldData['product_code']
            ),
            'groupSequence' => isset($oldData['group_sequence']) ? $oldData['group_sequence'] : '',
            'isKit' => 'N', //($action == 'A') ? $newData['is_kit'] : $oldData['is_kit'],
            'unitOfMeasureCode' => ($action == 'A') ? $newData['unit_of_measure_code'] : $oldData['unit_of_measure_code'],
            'quantity' => $newData['quantity'],
            'description' => isset($newData['description'])?$newData['description']:'',
            'price' => $price,
            'lineValue' => $lineValue,
            'taxCode' => isset($oldData['tax_code']) ? $oldData['tax_code'] : '',
            'additionalText' => $newData['additional_text'],
            'requestDate' => $this->getHelper()->getFormattedInputDate($newData['request_date'], self::DATE_FORMAT),
            'attributes' => array(),
            'attachments' => array()
        );
        
        if ($eccReturnType != '') {
            $line['_attributes']['returnType'] = $eccReturnType;
        }
        //<dealer> tag for DealerConnect
        if(isset($newData['dealer_price_inc'])){
            $basePrice = isset($newData['dp_base_price']) ? $newData['dp_base_price'] : 0;
            $price = $newData['dealer_price_inc'];
            $lineValue = ($newData['dealer_price_inc'] != 'TBC') ? ($newData['quantity'] * $newData['dealer_price_inc']) : 'TBC';
            $lineDiscount = isset($newData['dealer-discount']) ? $newData['dealer-discount'] : 0.00;
            $line['dealer'] = array(
                'basePrice' => $basePrice,
                'price' => $price,
                'priceInc' => $price,
                'lineValue'=> $lineValue,
                'lineValueInc'=> $lineValue,
                'lineDiscount' => $lineDiscount * $newData['quantity'],
                'taxCode' => isset($oldData['tax_code']) ? $oldData['tax_code'] : ''
            );
        }else{
            $line['dealer'] = array();
        }
        if (isset($newData['discount'])) {
            // divide by the percentage left after deducting the discount, not the discount itself
            $origPrice = $newData['sr_base_price'];
            $priceDiff = $origPrice - $newData['price'];
            $discountPercent = number_format((($priceDiff / $origPrice) * 100), 2, '.', '');
            $discountValue = number_format($priceDiff, 2, '.', '');
            $line['discount'] = array(
                'value' => $discountValue,
                'description' => 'Sales Rep Discount',
                'percent' => $discountPercent
            );
        } else {
            $line['discount'] = array('value' => null, 'description' => null, 'percent' => null);
        }

        if (isset($newData['attributes']) && !empty($newData['attributes'])) {
            if(is_string($newData['attributes'])){
                $line['attributes']['attribute'] = unserialize(base64_decode($newData['attributes']));
            }else{
                $line['attributes']['attribute'] = $newData['attributes'];
            }
        }

        $attachments = $this->getLineAttachments($rowIndex);

        $attachmentData = $this->_processSubData('attachments', array('attachments' => $attachments));

        if ($attachmentData) {
            $line['attachments'] = array('attachment' => $attachmentData);
        }

        if (!empty($oldData)) {
            $line['oldLine'] = array(
                'productCode' => array(
                    '_attributes' => array(
                        'type' => $productCodeType
                    ),
                    $oldData['product_code']['value']
                ),
                'groupSequence' => $oldData['group_sequence'],
                'unitOfMeasureCode' => $oldData ['unit_of_measure_code'],
                'quantity' => $oldData['quantity'],
                'description' => $oldData['description'],
                'additionalText' => $oldData['additional_text'],
                'requestDate' => $oldData['request_date'],
                'price' => $oldData['price'],
                'discount' => array_key_exists('discount', $oldData) ? $oldData['discount'] : null,
            );
        }

        $this->_subData['lines'][] = $line;
    }

    private function getLineAttachments($rowIndex)
    {
        $new = $this->getNewData();

        $attachments = array();

        if (isset($new['lineattachments']['existing'][$rowIndex])) {
            $attachments['existing'] = $new['lineattachments']['existing'][$rowIndex];
        }

        if (isset($new['lineattachments']['new'][$rowIndex])) {
            $attachments['new'] = $new['lineattachments']['new'][$rowIndex];
        }

        return $attachments;
    }

    private function getDealerSubtotal($lines)
    {
        $subtotal = 0;
        foreach ($lines as $line) {
            if (isset($line['dealer']) && !empty($line['dealer'])) {
                if (isset($line['dealer']['price'])) {
                    $subtotal += ($line['dealer']['price'] * $line['quantity']);
                }
            }
        }
        return $subtotal;
    }

}
