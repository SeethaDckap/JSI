<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SPOS - Supplier Purchase Order Search 
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Spos extends \Epicor\Supplierconnect\Model\Message\RequestsearchArray
{


    /**
     * Message Type
     */
    const MESSAGE_TYPE = 'SPOS';

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SPOS');
        $this->setConfigBase('supplierconnect_enabled_messages/SPOS_request/');
        $this->setResultsPath('purchaseOrders/purchaseOrder');
    }

}
