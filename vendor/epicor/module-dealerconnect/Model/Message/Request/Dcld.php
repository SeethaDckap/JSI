<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Message\Request;


/**
 * Request DCLD - Dealer Claim details enquiry  
 * 
 * Websales requesting search for quote for account
 * 
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Dcld extends \Epicor\Comm\Model\Message\Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('DCLD');
        $this->setLicenseType('Dealer_Portal');
        $this->setConfigBase('dealerconnect_enabled_messages/DCLD_request/');
        $this->setResultsPath('claim');
    }

    public function buildRequest()
    {
        $data = $this->getMessageTemplate();

        $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
        $data['messages']['request']['body']['caseNumber'] = $this->getCaseNumber();
        $data['messages']['request']['body']['languageCode'] = $this->getLanguageCode();

        $this->setOutXml($data);

        return true;
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

}
