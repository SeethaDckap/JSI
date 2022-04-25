<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SPLD - Supplier Parts List Detail Enquiry 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 * 
 * @method getProductCode()
 * @method setProductCode()
 * 
 * @method getOperationalCode()
 * @method setOperationalCode()
 * 
 * @method getEffectiveDate()
 * @method setEffectiveDate()
 * 
 * @method getUnitOfMeasureCode()
 * @method setUnitOfMeasureCode()
 */
class Spld extends \Epicor\Supplierconnect\Model\Message\Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SPLD');
        $this->setConfigBase('supplierconnect_enabled_messages/SPLD_request/');
    }

    public function buildRequest()
    {
        $data = $this->getMessageTemplate();
        $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
        $data['messages']['request']['body']['languageCode'] = $this->getLanguageCode();
        $data['messages']['request']['body']['productCode'] = $this->getProductCode();
        $data['messages']['request']['body']['operationalCode'] = $this->getOperationalCode();
        $data['messages']['request']['body']['effectiveDate'] = $this->getEffectiveDate();
        $data['messages']['request']['body']['unitOfMeasureCode'] = $this->getUnitOfMeasureCode();
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
