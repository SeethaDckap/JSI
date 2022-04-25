<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CRQD - Customer Quote details enquiry  
 * 
 * Websales requesting search for quote for account
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Crqd extends \Epicor\Customerconnect\Model\Message\Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('CRQD');
        $this->setConfigBase('customerconnect_enabled_messages/CRQD_request/');
        $this->setResultsPath('quote');
    }

    public function buildRequest()
    {
        $data = $this->getMessageTemplate();

        $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
        $data['messages']['request']['body']['quoteNumber'] = $this->getQuoteNumber();
        $data['messages']['request']['body']['quoteSequence'] = $this->getQuoteSequence();
        $data['messages']['request']['body']['languageCode'] = $this->getLanguageCode();

        $this->setOutXml($data);

        return true;
    }

}
