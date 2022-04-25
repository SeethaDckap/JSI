<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SUIS - Supplier Invoice Search
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Suis extends \Epicor\Supplierconnect\Model\Message\RequestsearchArray
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SUIS');
        $this->setConfigBase('supplierconnect_enabled_messages/SUIS_request/');
        $this->setResultsPath('invoices/invoice');
    }

}
