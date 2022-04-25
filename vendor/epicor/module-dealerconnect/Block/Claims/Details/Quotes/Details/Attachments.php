<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes\Details;


/**
 * Dealer Claim Quotes details attachments grid container
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Attachments extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Attachments
{

    const FRONTEND_RESOURCE_CREATE = "Dealer_Connect::dealer_claim_create";
    const FRONTEND_RESOURCE_EDIT = 'Dealer_Connect::dealer_claim_edit';

    public function _isFormAccessAllowed()
    {
        $allowed = true;
        $action = $this->getRequest()->getActionName();
        switch($action) {
            case 'new':
            case 'duplicate':
                $allowed = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_CREATE);
                break;
            case 'details':
                $allowed = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT);
                break;
            case 'quotedetails':
                $allowed = $this->_isAccessAllowed(static::FRONTEND_RESOURCE_EDIT);
                break;
        }
        return $allowed;
    }

}
