<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Quotes\Details;


/**
 * Quotes Details page buttons
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Buttons extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Buttons
{

    const FRONTEND_RESOURCE_CREATE = "Dealer_Connect::dealer_quotes_create";

    const FRONTEND_RESOURCE_EDIT = 'Dealer_Connect::dealer_quotes_edit';

    const FRONTEND_RESOURCE_CONFIRMREJECT = 'Dealer_Connect::dealer_quotes_confirmrejects';

    public function getConfirmUrl()
    {
        return $this->getUrl('dealerconnect/quotes/confirm');
    }

    public function getRejectUrl()
    {
        return $this->getUrl('dealerconnect/quotes/reject');
    }

}
