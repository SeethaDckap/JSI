<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CPHS - Customer Purchase History Search  
 * 
 * Websales requesting Recent Purchase History for account
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
class Cphs extends \Epicor\Customerconnect\Model\Message\Requestsearch
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('CPHS');
        $this->setConfigBase('customerconnect_enabled_messages/CPHS_request/');
        $this->setResultsPath('group/products/product');
        $this->setIsCurrency(true);
    }

}
