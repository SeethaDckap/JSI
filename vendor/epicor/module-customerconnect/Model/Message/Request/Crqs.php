<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Message\Request;


/**
 * Request CRQS - Customer Quote Search  
 * 
 * Websales requesting search for orders for account
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Crqs extends \Epicor\Customerconnect\Model\Message\Requestsearch
{

    protected function _construct()
    {
        parent::_construct();
        $this->setMessageType('CRQS');
        $this->setConfigBase('customerconnect_enabled_messages/CRQS_request/');
        $this->setResultsPath('quotes/quote');
        $this->setIsCurrency('true');
        $this->setQuotesEnteredVisibility();
    }

    private function isOnlyQuotesEnteredVisible(): bool
    {
        return (boolean) $this->getConfig('quote_entered');
    }

    private function setQuotesEnteredVisibility()
    {
        if ($this->isOnlyQuotesEnteredVisible()) {
            $this->addSearchOption('quoted', 'EQ', 'Y');
        }
        return;
    }
}
