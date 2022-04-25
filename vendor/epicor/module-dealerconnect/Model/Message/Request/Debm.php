<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Model\Message\Request;


/**
 * Request DEBM - Dealer Bill Of Material Search
 * 
 * Websales requesting search for orders for account
 *
 * @category   Epicor
 * @package    Epicor_DealersPortal
 * @author     Epicor Websales Team
 */
class Debm extends \Epicor\Dealerconnect\Model\Message\Request\Inventory\Requestsearch
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('DEBM');
        $this->setLicenseType('Dealer_Portal');
        $this->setConfigBase('dealerconnect_enabled_messages/DEBM_request/');
        $this->setResultsPath('materials/material');
        $this->setTransPath('material_trans/material_tran');
        $this->setIsCurrency(true);
    }
    
    public function buildRequest()
    {
        $data = $this->getMessageTemplate();

        $data['messages']['request']['body']['accountNumber'] = $this->getAccountNumber();
        $data['messages']['request']['body']['locationNumber'] = $this->getLocationNumber();

        $this->setOutXml($data);

        return true;
    }
    
    public function processResponse()
    {
        if ($this->getIsSuccessful()) {
            $this->setResults($this->getResponse()->getVarienDataFromPath($this->getResultsPath()));
            $this->setTransResults($this->getResponse()->getVarienDataFromPath($this->getTransPath()));
            return true;
        } else {
            return false;
        }
    }

}
