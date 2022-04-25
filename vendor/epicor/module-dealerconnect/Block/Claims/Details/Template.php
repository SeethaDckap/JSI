<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details;


/**
 * Dealerconnect Dealer Claim Block Template
 * 
 * @category   Epicor
 * @package    Dealerconnect
 * @author     Epicor Websales Team
 */
class Template extends \Epicor\AccessRight\Block\Template
{
    const FRONTEND_RESOURCE_CREATE = "Dealer_Connect::dealer_claim_create";
    const FRONTEND_RESOURCE_EDIT = 'Dealer_Connect::dealer_claim_edit';
    const FRONTEND_RESOURCE_CONFIRMREJECT = 'Dealer_Connect::dealer_claim_confirmrejects';
}
