<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Message\Request;


/**
 * Request CUOS - Customer Order Search  
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
 * @package    Epicor_DealersPortal
 * @author     Epicor Websales Team
 */
class Deis extends \Epicor\Dealerconnect\Model\Message\Request\Inventory\Requestsearch
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('DEIS');
        $this->setLicenseType('Dealer_Portal');
        $this->setConfigBase('dealerconnect_enabled_messages/DEIS_request/');
        $this->setResultsPath('location_inventorys/location_inventory');
        $this->setIsCurrency(true);
    }

}
