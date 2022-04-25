<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SUPS - Supplier Payment Search
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Sups extends \Epicor\Supplierconnect\Model\Message\RequestsearchArray
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SUPS');
        $this->setConfigBase('supplierconnect_enabled_messages/SUPS_request/');
        $this->setResultsPath('payments/payment');
    }

}
