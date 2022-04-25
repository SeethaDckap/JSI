<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SPLS - Supplier Parts List Search 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Spls extends \Epicor\Supplierconnect\Model\Message\RequestsearchArray
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SPLS');
        $this->setConfigBase('supplierconnect_enabled_messages/SPLS_request/');
        $this->setResultsPath('parts/part');
    }

}
