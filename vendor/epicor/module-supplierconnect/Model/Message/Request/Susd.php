<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SUSD - Supplier Summary Details  
 * 
 * Websales requesting search for orders for account
 * 
 * XML Data Support - Request
 * /brand/company                                           - supported
 * /brand/branch                                            - supported
 * /brand/warehouse                                         - supported
 * /brand/group                                             - supported 
 * /accountNumber                                           - supported
 * 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Susd extends \Epicor\Supplierconnect\Model\Message\Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SUSD');
        $this->setConfigBase('supplierconnect_enabled_messages/SUSD_request/');
    }

    public function buildRequest()
    {
        if ($this->getAccountNumber()) {
            $data = $this->getMessageTemplate();
            $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
            $this->setOutXml($data);

            return true;
        } else {
            return 'Missing account number';
        }
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
