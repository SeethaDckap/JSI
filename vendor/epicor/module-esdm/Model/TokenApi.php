<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Esdm\Model;


/**
 * ESDM token API
 * 
 * handles calling the WSDL service for ESDM
 * 
 * @category    Epicor
 * @package     Epicor_Esdm
 * @author      Epicor Web Sales Team
 */
use Magento\Payment\Gateway\Http\Client\Soap;

class TokenApi
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

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Esdm\Logger\Logger $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }
    private function getLive()
    {
        return $this->scopeConfig->getValue('payment/esdm/live_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == self::LIVE_MODE;
    }
    
    private function getDemoMode()
    {
        return $this->scopeConfig->getValue('payment/esdm/live_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == self::DEMO_MODE;
    }

    /**
     * Requests a CVV token
     * 
     * @param string $data
     * 
     * @return string
     */
    public function cvvTokenRequest($data)
    {
        return $this->process('TEMP', $data);
    }

    /**
     * Requests a CCV token
     * 
     * @param string $data
     * 
     * @return string
     */
    public function ccvTokenRequest($data)
    {
        return $this->process('CC', $data);
    }

    /**
     * Processes the send request, repeating attempts 3 times or  until a result is given
     * 
     * @param type $dataClass
     * @param type $data
     * 
     * @return string
     */
    private function process($dataClass, $data)
    {
        $debug = false;

        for ($i = 1; $i <= 3; $i++) {
            if ($debug) {
                $this->logger->info('Send Attempt '.$i);
            }
            $result = $this->send($dataClass, $data);
            if ($debug) {
                $this->logger->info($result);
            }

            if (!is_null($result))
                break;

            sleep(1);
        }
        return $result;
    }

    /**
     * 
     * 
     * Sends the request to ESDM using soap client
     * 
     * @param string $dataClass
     * @param string $data
     * 
     * @return string
     */
    private function send($dataClass, $data)
    {
        
        if ($this->getDemoMode()) {
            $token = '==';
            $values = 'qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM';
            while(strlen($token) < 47) {
                $token .= substr($values, rand(0,strlen($values)), 1);
            }
            $token .= str_pad($dataClass, 5, '-');
            $token .= '##';
            return $token;
        }
        
        if($this->getLive()) {
            $url = $this->scopeConfig->getValue('payment/esdm/live_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            $url = $this->scopeConfig->getValue('payment/esdm/test_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        $result = false;

        try {
            //Access the secure data service's WSDL
            try {
                $context = stream_context_create(array(
                        'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                        )
                ));                 
                $client = new \SoapClient($url, array('stream_context' => $context));
                $request = $client->SaveSecureData(
                        array(
                            'dataToBeSecurlyStored' => $data,
                            'dataClass' => $dataClass
                        )
                );
                $result = $request->SaveSecureDataResult;
            } catch (SoapFault $e) {
                //Redirect to error page upon a soap fault
                $this->logger->info($e->getMessage());
                $result = 'An error occurred when using ESDM payment method';
            }
        } catch (\Exception $e) {
            //Redirect to error page upon error
            $this->logger->info($e->getMessage());
            $result = 'An error occurred when using ESDM payment method';
        }

        return $result;
    }

}
