<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SURD - Supplier RFQ Details  
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 * 
 * @method setRfqNumber()
 * @method getRfqNumber()
 * 
 * @method setLine()
 * @method getLine()
 */
class Surd extends \Epicor\Supplierconnect\Model\Message\Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SURD');
        $this->setConfigBase('supplierconnect_enabled_messages/SURD_request/');
    }

    public function buildRequest()
    {
        $data = $this->getMessageTemplate();
        $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
        $data['messages']['request']['body']['languageCode'] = $this->getLanguageCode();
        $data['messages']['request']['body']['rfqNumber'] = $this->getRfqNumber();
        $data['messages']['request']['body']['line'] = $this->getLine();
        $this->setOutXml($data);

        return true;
    }

    public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            $this->setResults($this->getResponse());
            return true;
        } else {
            return false;
        }
    }

}
