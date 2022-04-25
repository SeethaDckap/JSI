<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Controller\Claims;

class QuoteDuplicate extends \Epicor\Customerconnect\Controller\Rfqs\Duplicate
{
    const FRONTEND_RESOURCE_CLAIM_QUOTE_CREATE = 'Dealer_Connect::dealer_claim_create';
    const FRONTEND_RESOURCE_CLAIM_QUOTE_EDIT = 'Dealer_Connect::dealer_claim_edit';

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CLAIM_QUOTE_CREATE)
            || $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CLAIM_QUOTE_EDIT);
    }

}
