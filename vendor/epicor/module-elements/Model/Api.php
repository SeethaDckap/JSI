<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Model;



class Api
{
    
    const TEST_MODE = 0;
    const LIVE_MODE = 1;
    const DEMO_MODE = 2;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;


    public $xml_message = null;
    public $_error = null;
    public $xml_response = null;
    public $msgData_response = null;
    public $processingDB_response = null;
    public $msgdata_values = array();
    public $httpCode = null;
    public $requesttag = null;
    public $Live = true;
    private $message_template = array();

        
    
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Epicor\Elements\Logger\Logger $logger, \Epicor\Common\Helper\Xml $commonXmlHelper)
    {
        $this->scopeConfig     = $scopeConfig;
        $this->logger          = $logger;
        $this->commonXmlHelper = $commonXmlHelper;
        $this->refreshTemplate();
    }
    
    protected function refreshTemplate()
    {
        $this->message_template = array();
        
        $this->addElement('Credentials', array(
            'AccountID' => $this->scopeConfig->getValue('payment/elements/AccountID', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'AccountToken' => $this->scopeConfig->getValue('payment/elements/AccountToken', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'AcceptorID' => $this->scopeConfig->getValue('payment/elements/AcceptorID', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        ));
        $this->addElement('Application', array(
            'ApplicationID' => $this->scopeConfig->getValue('payment/elements/ApplicationID', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'ApplicationName' => $this->scopeConfig->getValue('payment/elements/ApplicationName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'ApplicationVersion' => $this->scopeConfig->getValue('payment/elements/ApplicationVersion', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        ));
    }
    
    /**
     * Check if payment gateway is set to LIVE mode
     * 
     * @return bool
     */
    public function isLiveMode()
    {
        return $this->isMode(self::LIVE_MODE);
    }
    
    /**
     * Check if payment gateway is set to TEST mode
     * 
     * @return bool
     */
    public function isTestMode()
    {
        return $this->isMode(self::TEST_MODE);
    }
    
    /**
     * Check if payment gateway is set to DEMO mode
     * 
     * @return bool
     */
    public function isDemoMode()
    {
        return $this->isMode(self::DEMO_MODE);
    }
    
    protected function isMode($mode = self::LIVE_MODE)
    {
        return $this->scopeConfig->getValue('payment/elements/live_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == $mode;
    }
    

    
    /**
     * Setup a Elements transaction
     * 
     * @return string | array
     */
    public function transactionSetup()
    {
        return $this->process('TransactionSetup', 'transaction');
    }
    
    /**
     * Authorize credit card payment
     * 
     * @return string | array
     */
    public function creditCardAuthorization()
    {
        $this->xml_message = null;
        return $this->process('CreditCardAuthorization', 'transaction');
    }
    
    /**
     * Authorize credit card payment
     * 
     * @return string | array
     */
    public function creditCardAvsOnly()
    {
        $this->xml_message = null;
        return $this->process('CreditCardAVSOnly', 'transaction');
    }
    
    /**
     * Retrieve Payment Account Details
     * 
     * @return string | array
     */
    public function paymentAccountQuery()
    {
        $this->xml_message = null;
        return $this->process('PaymentAccountQuery', 'services');
    }
    
    /**
     * Create Payment Account from Transaction Id
     * 
     * @return string | array
     */
    public function paymentAccountCreateFromTransactionId()
    {
        $this->xml_message = null;
        return $this->process('PaymentAccountCreateWithTransID', 'services');
    }
    
    /**
     * Delete Payment Account
     * 
     * @return string | array
     */
    public function paymentAccountDelete()
    {
        $this->xml_message = null;
        return $this->process('PaymentAccountDelete', 'services');
    }
    
    /**
     * Add Xml Element to the message
     * 
     * @param strng $key
     * @param mixed $value
     * @return \Epicor\Elements\Model\Api
     */
    public function addElement($key = null, $value = null)
    {
        $this->message_template[$key] = $value;
        return $this;
    }
    
    /**
     * Remove Xml Element from the message
     * 
     * @param strng $key
     * @return \Epicor\Elements\Model\Api
     */
    public function removeElement($key = null)
    {
        unset($this->message_template[$key]);
        return $this;
    }
    
    /**
     * Send Xml message 
     * 
     * @param string $url
     * @return string
     */
    public function send($url = 'https://txn-test.cxmlpg.com/XML4/commideagateway.asmx')
    {
        //      if($this->Live)
        //$url = 'https://txn.cxmlpg.com/XML4/commideagateway.asmx';
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xmlmessage());
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: text/xml",
            "Content-length: " . strlen($this->xmlmessage())
        ));
        $this->xml_response = curl_exec($ch);
        $this->httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->logger->info('Sent :' . "\n" . $this->xmlmessage());
        $this->logger->info('Recieved :' . "\n" . $this->xml_response);
        return $this->xml_response;
    }
    
    /**
     * Check xml response for errors
     * 
     * @return string
     */
    public function error()
    {
        if ($this->_error == null) {
            $errormsg = '';
            try {
                
                
                $xmlDoc = new \DOMDocument();
                $xmlDoc->loadXML($this->xml_response);
                $responseMessage = $xmlDoc->getElementsByTagName('ExpressResponseMessage')->item(0)->nodeValue;
                $responseCode    = $xmlDoc->getElementsByTagName('ExpressResponseCode')->item(0)->nodeValue;
                
                if (!in_array($responseCode, array(
                    0
                ))) {
                    $errormsg = $responseCode;
                    $errormsg .= " : ";
                    $errormsg .= $responseMessage;
                }
                $this->_error = $errormsg;
            }
            catch (Exception $e) {
                echo $this->_error = $e->getMessage();
                $this->logger->info(\Monolog\Logger::ERROR, $e);
            }
        }
        return $this->_error;
    }
    
    /**
     * Set Message Type in the xml message
     * 
     * @param string $msgType
     * @param string $urlType
     * @return \Epicor\Elements\Model\Api
     */
    private function msgtype($msgType = null, $urlType = 'transaction')
    {
        if ($msgType != null) {
            $this->message_template                                   = array(
                $msgType => $this->message_template
            );
            $this->message_template[$msgType]['_attributes']['xmlns'] = 'https://' . $urlType . '.elementexpress.com';
        }
        return $this;
    }
    
    /**
     * get xml message or convert message template array to xml message string
     * 
     * @return string
     */
    private function xmlmessage()
    {
        if ($this->xml_message == null) {
            $this->xml_message = $this->commonXmlHelper->convertArrayToXml($this->message_template);
            
            $this->refreshTemplate();
        }
        return $this->xml_message;
    }
    
    /**
     * send xml message and process response
     * 
     * @param string $msgType
     * @param string $urlType
     * @return string | array
     */
    private function process($msgType = null, $urlType = 'transaction')
    {
        try {
            
            $this->xml_message  = null;
            $this->xml_response = null;
            $this->httpCode     = null;
            
            $this->msgtype($msgType, $urlType);
            
            $prefix   = $this->isTestMode() ? 'cert' : '';
            $response = $this->send('https://' . $prefix . $urlType . '.elementexpress.com');
            
            $responseData = null;
            
            if ($this->error() != '')
                $responseData = $this->error();
            else {
                
                $xmlDoc = new \DOMDocument();
                $xmlDoc->loadXML($this->xml_response);
                
                if ($xmlDoc->getElementsByTagName('Response')->length > 0) {
                    $responseData = $this->getElementData($xmlDoc->getElementsByTagName('Response')->item(0));
                } else
                    $responseData = "Error : Elements Server Invalid Response - HTTP Status Code : " . $this->httpCode;
            }
        }
        catch (Exception $e) {
            $responseData = "Error : Elements Server Invalid Response - HTTP Status Code : " . $this->httpCode;
            $this->logger->info(\Monolog\Logger::ERROR, $e);
        }
        return $responseData;
    }
    
    public function getElementData($element)
    {
        if ($element->getElementsByTagName('*')->length == 0) {
            return $element->nodeValue;
        }
        
        $data = array();
        
        $subElements = $element->getElementsByTagName('*');
        foreach ($subElements as $subElement) {
            $data[$subElement->nodeName] = $this->getElementData($subElement);
        }
        
        return $data;
    }
    
}