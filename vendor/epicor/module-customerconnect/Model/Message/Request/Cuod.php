<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CUOD - Customer Order Search  
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
class Cuod extends \Epicor\Customerconnect\Model\Message\Request
{
    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('CUOD');
        $this->setConfigBase('customerconnect_enabled_messages/CUOD_request/');
        $this->setResultsPath('order');
        $this->setIsOrder('true');
        $this->setLicenseType(array('Customer', 'Consumer'));
    }

}
