<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Message\Request;


/**
 * Request DCLU - Dealer Claim Update  
 * 
 * @method setCaseNumber()
 * @method getCaseNumber()
 * @method getNewData()
 * @method setNewData()
 * @method setOldData()
 * @method getOldData()
 * 
 */
class Dclu extends \Epicor\Comm\Model\Message\Request
{
    private $_lineNumber = 1;
    private $_attachmentNumber = 0;
    private $_subData = array();
    private $_updateType = 'full';
    private $_isCustomerSalesRep = false;
    
    /**
     * @var \Epicor\Dealerconnect\Helper\Messaging
     */
    protected $dealerconnectHelper;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $directoryRegionFactory;
    
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;
    
    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;
    
    /**
     * @var \Epicor\Customerconnect\Model\Message\Request\Crqu
     */
    protected $customerconnectMessageRequestCrqu;
    
    /**
     * @var \Epicor\Customerconnect\Helper\Rfq
     */
    protected $customerconnectRfqHelper;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    
    /**
     *
     * @var \\Epicor\Comm\Model\Customer
     */
    protected $_customer;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;
    
    /**
     * 
     * @param \Epicor\Comm\Model\Context $context
     * @param \Epicor\Dealerconnect\Helper\Messaging $dealerconnectHelper
     * @param \Magento\Directory\Model\RegionFactory $directoryRegionFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Epicor\Customerconnect\Model\Message\Request\Crqu $customerconnectMessageRequestCrqu
     * @param \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Epicor\Comm\Model\Context $context,
        \Epicor\Dealerconnect\Helper\Messaging $dealerconnectHelper,
        \Magento\Directory\Model\RegionFactory $directoryRegionFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Customerconnect\Model\Message\Request\Crqu $customerconnectMessageRequestCrqu,
        \Epicor\Customerconnect\Helper\Rfq $customerconnectRfqHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->dealerconnectHelper = $dealerconnectHelper;
        $this->directoryRegionFactory = $directoryRegionFactory;
        $this->_localeResolver = $localeResolver;
        $this->commHelper = $context->getCommHelper();
        $this->customerconnectMessageRequestCrqu = $customerconnectMessageRequestCrqu;
        $this->customerconnectRfqHelper = $customerconnectRfqHelper;
        $this->messageManager = $messageManager;
        parent::__construct($context, $resource, $resourceCollection, $data);
        
        $this->setAccountNumber($this->commHelper->getErpAccountNumber());
        $this->setStore($this->storeManager->getStore()->getId());
        $this->setLanguageCode($this->dealerconnectHelper->getLanguageMapping($this->_localeResolver->getLocale()));
        $this->setLicenseType('Dealer_Portal');
        $this->setMessageType('DCLU');
        $this->setConfigBase('dealerconnect_enabled_messages/DCLU_request/');
        $this->setResultsPath('claim');
        $this->_customer = $this->commHelper->getCustomer();
        $this->_isCustomerSalesRep = $this->_customer->isSalesRep();
        $this->_salesRepId = $this->_customer->getEccSalesRepId();
        $this->_localeDate = $localeDate;
    }

    public function buildRequest()
    {
        $session = $this->customerSession;
        $customer = $session->getCustomer();
        $this->_lineNumber = 1;
        $this->_attachmentNumber = 0;
        //   don't proceed if account number not valid 
        if ($this->getAccountNumber()) {
            $message = $this->getMessageTemplate();

            $new = $this->getNewData();
            $old = $this->getOldData();
            $oldQuoteData = $this->getOldQuoteData();
            
            $deliveryMethod = $claimComment = '';
            $message['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
            $message['messages']['request']['body']['languageCode'] = $this->getLanguageCode();
            
            if (isset($new['delivery_method'])) {
                if ($new['delivery_method'] == 'other') {
                    $deliveryMethod = $new['delivery_method_other'];
                } else {
                    $deliveryMethod = $this->getHelper()->getShippingMethodMapping(
                        $new['delivery_method'], \Epicor\Comm\Helper\Messaging::MAGENTO_TO_ERP, false
                    );
                }
            }
            if (isset($new['claim_comment']) && $new['claim_comment'] != "") {
                $dateTimeFormat = $this->getDateTimeFormat();
                $currentDatetime = $this->_localeDate->date()->format($dateTimeFormat);
                $claimComment = $this->_customer->getName() . " - " . $currentDatetime . ": " . $new['claim_comment'];
            }
            if (isset($old['web_comment'])) {
                $claimComment = $old['web_comment'] . "\n" . $claimComment;
            }
            $baseCurrency = $this->storeManager->getStore()->getBaseCurrencyCode();
            $currencyCode = isset($old['currency_code']) ? $old['currency_code'] : $baseCurrency;
            $currency = $this->getHelper()->getCurrencyMapping($currencyCode, \Epicor\Comm\Helper\Messaging::MAGENTO_TO_ERP);
            
            $rfq = array();
            $claim = array(
                '_attributes'       => array(
                    'action'        => $this->getAction()
                ),
                'caseNumber'        => isset($new['case_number']) ? $new['case_number'] : '',
                'locationNumber'    => isset($new['locationNumber']) ? $new['locationNumber'] : '',
                'caseComment'       => $claimComment,
                'attachments'       => array(),
                'quote'             => array(),
            );
            
            if ($customer->isDealer()) {
                $claim['_attributes']['dealer'] = "Y";
            }
            if (empty($oldQuoteData)) {
                $lines = $this->_processSubData('lines', $new);
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

                $rfqAttachments = $this->_processSubData('attachments', $new);
                if ($rfqAttachments) {
                    $rfq['attachments'] = array('attachment' => $rfqAttachments);
                }


                $attachments = $this->_processSubData('claimattachments', $new);

                if ($attachments) {
                    $claim['attachments'] = array('attachment' => $attachments);
                }

                if (!empty($rfq)) {
                    $claim['quote'] = array(
                        '_attributes' => array(
                            'action' => (isset($new['quote_number']) && $new['quote_number'] != "")? 'U' : 'A',
                        ),
                        'quoteNumber' => isset($new['quote_number']) ? $new['quote_number'] : '',
                        'webReference' => isset($new['web_reference']) ? $new['web_reference'] : '',
                        'quoteDate' => isset($old['quote_date']) ? $old['quote_date'] : '',
                        'requiredDate' => $this->getHelper()->getFormattedInputDate($new['required_date'], self::DATE_FORMAT),
                        'customerReference' => $new['customer_reference'],
                        'salesReps' => isset($rfq['salesReps']) ? $rfq['salesReps'] : array(),
                        'salesRepId' => $this->_isCustomerSalesRep ? $this->_salesRepId : '',
                        'contacts' => isset($rfq['contacts']) ? $rfq['contacts'] : array(),
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
                        'carriageAmount' => isset($old['carriage_amount']) ? $old['carriage_amount'] : '',
                        'taxAmount' => isset($old['tax_amount']) ? $old['tax_amount'] : '',
                        'grandTotal' => isset($old['grand_total']) ? $old['grand_total'] : '',
                        'quoteStatus' => isset($old['quote_status']) ? $old['quote_status'] : '',
                        'quoteEntered' => isset($old['quote_entered']) ? $old['quote_entered'] : '',
                        'noteText' => $new['note_text'],
                        'dealer' => array(
                            'goodsTotal' => isset($old['goods_total']) ? $old['goods_total'] : '',
                            'goodsTotalInc' => isset($old['goods_total']) ? $old['goods_total'] : '',
                            'discountAmount' => isset($old['discount']['value']) ? $old['discount']['value'] : '',
                            'carriageAmount' => isset($old['carriage_amount']) ? $old['carriage_amount'] : '',
                            'carriageAmountInc' => isset($old['carriage_amount']) ? $old['carriage_amount'] : '',
                            'grandTotal' => isset($old['grand_total']) ? $old['grand_total'] : '',
                            'grandTotalInc' => isset($old['grand_total']) ? $old['grand_total'] : '',
                        ),
                        'orderFor' => $this->_getAddressData($new['quote_address'], 'quote'),
                        'deliveryAddress' => $this->_getAddressData($new['delivery_address'], 'delivery'),
                        'lines' => isset($rfq['lines']) ? $rfq['lines'] : array(),
                        'attachments' => isset($rfq['attachments']) ? $rfq['attachments'] : array(),
                    );
                }
            } else {
                $crqu = $this->customerconnectMessageRequestCrqu;
                $cucoRfqHelper = $this->customerconnectRfqHelper;
                if ($crqu->isActive() && $cucoRfqHelper->getMessageType('CRQU')) {
                    $files = $new['rfq_files'];
                    $crqu->setAction('U');
                    $crqu->setQuoteNumber($new['quote_number']);
                    $crqu->setQuoteSequence($new['quote_sequence']);
                    $crqu->setOldData($oldQuoteData);
                    $crqu->setNewData($new);
                    if ($crqu->sendMessage()) {
                        $rfq = $crqu->getResults();
                        $cucoRfqHelper->processCrquFilesSuccess($files, $rfq);
                    } else {
                        $cucoRfqHelper->processCrquFilesFail($files);
                        $error = __('Quote update request failed');
                        $this->messageManager->addErrorMessage($error);
                    }
                }
            }
            $message['messages']['request']['body']['claim'] = $claim;
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
        return $this->_getAddressData($newData[$type . '_address'], $type);
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
        $helper = $this->dealerconnectHelper;
        /* @var $helper \Epicor\Dealerconnect\Helper\Messaging */
        if (isset($data['county_id'])) {
            $region = $this->directoryRegionFactory->create()->load($data['county_id']);
            /* @var $region Mage_Directory_Model_Region */
            $data['county'] = $helper->getRegionNameOrCode($region->getCountryId(), $region->getCode());
        }

        $defaultAddCode = $this->scopeConfig->getValue('epicor_comm_enabled_messages/global_request/default_address_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $addCode = isset($data['address_code']) && !empty($data['address_code']) ? $data['address_code'] : $defaultAddCode;
        $name = isset($data['company']) && !empty($data['company']) ? $data['company'] : $data['name'];

        $addressData = array(
            'contactName' => $name,
            'addressCode' => $addCode,
            'name' => $name,
            'address1' => $data['address1'],
            'address2' => $data['address2'],
            'address3' => @$data['address3'],
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
            case 'attachments': 
            case 'claimattachments': 
                $this->addAttachmentArray($type, $rowIndex, $action, $newData, $oldData);
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
    private function addAttachmentArray($type, $rowIndex, $action, $newData, $oldData = null)
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
        $this->_subData[$type][] = $attachment;
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
            $eccReturnType = isset($oldData['_attributes']['returnType']) ? $oldData['_attributes']['returnType'] : '';
            $this->_lineNumber = ($number > $this->_lineNumber) ? $number + 1 : $this->_lineNumber;
        } else {
            $number = $this->_lineNumber;
            $this->_lineNumber++;
            $type = 'N';
            $productCodeType = $newData['type'];
            $eccReturnType = isset($newData['ecc_return_type']) ? $newData['ecc_return_type'] : '';
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
            'price' => ($action == 'A' || ($this->_isCustomerSalesRep && isset($newData['price']))) ? $newData['price'] : $oldData['price'],
            'lineValue' => ($action == 'A' || ($this->_isCustomerSalesRep && isset($newData['line_value']))) ? $newData['line_value'] : $oldData['line_value'],
            'taxCode' => isset($oldData['tax_code']) ? $oldData['tax_code'] : '',
            'additionalText' => $newData['additional_text'],
            'requestDate' => $this->getHelper()->getFormattedInputDate($newData['request_date'], self::DATE_FORMAT),
            'attributes' => array(),
            'dealer' => array(
                'basePrice' => ($action == 'A' || ($this->_isCustomerSalesRep && isset($newData['price']))) ? $newData['price'] : $oldData['price'],
                'price' => ($action == 'A' || ($this->_isCustomerSalesRep && isset($newData['price']))) ? $newData['price'] : $oldData['price'],
                'priceInc' => ($action == 'A' || ($this->_isCustomerSalesRep && isset($newData['price']))) ? $newData['price'] : $oldData['price'],
                'lineValue' => ($action == 'A' || ($this->_isCustomerSalesRep && isset($newData['line_value']))) ? $newData['line_value'] : $oldData['line_value'],
                'lineValueInc' => ($action == 'A' || ($this->_isCustomerSalesRep && isset($newData['line_value']))) ? $newData['line_value'] : $oldData['line_value'],
                'lineDiscount' => '',
                'taxCode' => '',
            ),
            'attachments' => array()
        );
        if ($eccReturnType != '') {
            $line['_attributes']['returnType'] = $eccReturnType;
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
            $line['dealer']['lineDiscount'] = $discountValue;
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

    public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            // getVarienDataFromPath converts xml into a varien object, which can be referenced from controller
            $this->setResults($this->getResponse()->getVarienDataFromPath($this->getResultsPath()));
            return true;
        } else {
            return false;
        }
    }
    
     /**
     * Sends emails to admin
     * 
     * @param array $data
     * @return void
     */
    public function sendEmail($data)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $enabled = $this->scopeConfig->getValue(
                    'dealerconnect_enabled_messages/DCLU_request/send_claims_update_emails', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );
        if ($enabled) {
            $helper = $this->dealerconnectHelper;
            /* @var $helper \Epicor\Dealerconnect\Helper\Messaging */

            $template = $this->scopeConfig->getValue(
                    'dealerconnect_enabled_messages/DCLU_request/claims_update_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );

            $from = $this->scopeConfig->getValue(
                    'dealerconnect_enabled_messages/DCLU_request/claim_update_email_sender', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );

            $to = $this->scopeConfig->getValue(
                    'dealerconnect_enabled_messages/DCLU_request/claim_update_email_recipient', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );

            $toEmail = $this->scopeConfig->getValue(
                    'trans_email/ident_' . $to . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );

            $name = $this->scopeConfig->getValue(
                    'trans_email/ident_' . $to . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );
            $data['name'] = $name;
            $helper->sendTransactionalEmail($template, $from, $toEmail, $name, $data, $storeId);
        }
        return;
    }
    
    public function getDateTimeFormat()
    {
        $format = '';
        $storeId = $this->storeManager->getStore()->getId();
        $dateFormat = $this->scopeConfig->getValue(
                    'dealerconnect_enabled_messages/DCLU_request/date_fields_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );
        $timeFormat = $this->scopeConfig->getValue(
                    'dealerconnect_enabled_messages/DCLU_request/time_format', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
            );
        $format = str_replace("y", "Y", str_replace(",", "/", $dateFormat));
        switch($timeFormat) {
            case '12h':
                $format .= " h:i:s A";
                break;
            case '24h':
                $format .= " H:i:s";
                break;
        }
        return $format;
    }
}
