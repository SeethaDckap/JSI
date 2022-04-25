<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SPOD - Supplier Purchase Order Detail Enquiry 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 * 
 * @method getPurchaseOrderNumber
 * @method setPurchaseOrderNumber
 */
class Spod extends \Epicor\Supplierconnect\Model\Message\Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SPOD');
        $this->setConfigBase('supplierconnect_enabled_messages/SPOD_request/');
    }

    public function buildRequest()
    {
        $data = $this->getMessageTemplate();
        $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
        $data['messages']['request']['body']['languageCode'] = $this->getLanguageCode();
        $data['messages']['request']['body']['purchaseOrderNumber'] = $this->getPurchaseOrderNumber();
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
