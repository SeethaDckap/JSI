<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Supplierconnect\Model\Message\Request;


/**
 * Request SPCS - Supplier Purchase Order Changes Search
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Spcs extends \Epicor\Supplierconnect\Model\Message\RequestsearchArray
{
    /**
     * Message Type
     */
    const MESSAGE_TYPE = 'SPCS';

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('SPCS');
        $this->setConfigBase('supplierconnect_enabled_messages/SPCS_request/');
        $this->setResultsPath('purchaseOrders/purchaseOrder');
    }

}
