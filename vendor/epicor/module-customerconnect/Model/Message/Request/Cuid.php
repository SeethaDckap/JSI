<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CUID - Customer Order Search  
 * 
 * Websales requesting search for orders for account
 * 
 * XML Data Support - Request
 * /brand/company                                           - supported
 * /brand/branch                                            - supported
 * /brand/warehouse                                         - supported
 * /brand/group                                             - supported 
 * /accountNumber                                           - supported
 * /orderNumber                                             - supported
 * /languageCode                                            - supported


 * 
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Cuid extends \Epicor\Customerconnect\Model\Message\Request
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('CUID');
        $this->setConfigBase('customerconnect_enabled_messages/CUID_request/');
        $this->setResultsPath('invoice');
        $this->setIsInvoice('true');
    }

}
