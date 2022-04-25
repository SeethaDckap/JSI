<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CAPS - Customer Account AR Payments
 * 
 * 
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Caps extends \Epicor\Customerconnect\Model\Message\Requestsearch
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('CAPS');
        $this->setRequestMessageBody(true);
        $this->setIsCurrency(true);
        $this->setConfigBase('customerconnect_enabled_messages/CAPS_request/');
        //If the request is for Search then the result path should get changed
        //Because we are not loading the complete page
        if($this->request->getParam('arfiltergrid') =="true") {
            $this->setResultsPath('body/invoices/invoice');
        } else {
            //This Resultspath will go when we are landing on the page
            $this->setResultsPath('body');
        }
    }

}
