<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CUPS - Customer Order Search  
 * 
 * Websales requesting search for orders for account
 * 
 * XML Data Support - Request
 * /brand/company                                           - supported
 * /brand/branch                                            - supported
 * /brand/warehouse                                         - supported
 * /brand/group                                             - supported 
 * /results/maxResults                                      - supported
 * /results/rangeMin                                        - supported
 * /results/searches/search/criteria                        - supported
 * /results/searches/search/condition                       - supported
 * /results/searches/search/value                           - supported
 * /accountNumber                                           - supported 
 * /languageCode                                            - supported   
 * /currencies/currency/currencyCode                        - supported
 * 

 * 
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Cups extends \Epicor\Customerconnect\Model\Message\Requestsearch
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('CUPS');
        $this->setConfigBase('customerconnect_enabled_messages/CUPS_request/');
        $this->setResultsPath('payments/payment');
        $this->setIsCurrency(true);
    }

}
