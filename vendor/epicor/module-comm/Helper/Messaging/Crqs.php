<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Helper\Messaging;


/**
 * Crqs Messaging Helper
 * 
 * @author     Epicor Websales Team
 */
class Crqs extends \Epicor\Comm\Helper\Messaging
{

    public function mutipleAccountsEnabled()
    {
        return $this->scopeConfig->isSetFlag('customerconnect_enabled_messages/CRQS_request/multiple_accounts', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
