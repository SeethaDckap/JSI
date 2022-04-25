<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CUSS - Customer Shipment Search  
 * 
 * Websales requesting search for orders for account
 * 
 * XML Data Support - Request
 * /brand/company                                           - supported
 * /brand/site                                              - supported
 * /brand/warehouse                                         - supported
 * /brand/group                                             - supported  
 * /results/maxResults                                      - supported
 * /results/rangeMin                                        - supported
 * /results/searches/search/criteria                        - supported
 * /results/searches/search/condition                       - supported
 * /results/searches/search/value                           - supported
 * /accountNumber                                           - supported 
 * /languageCode                                            - supported   

 * 
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Cuss extends \Epicor\Customerconnect\Model\Message\Requestsearch
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('CUSS');
        $this->setConfigBase('customerconnect_enabled_messages/CUSS_request/');
        $this->setResultsPath('shipments/shipment');
    }

}
