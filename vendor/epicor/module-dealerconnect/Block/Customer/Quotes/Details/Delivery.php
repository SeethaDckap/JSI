<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Quotes\Details;


/**
 * RFQ delivery address block
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Delivery extends \Epicor\Customerconnect\Block\Customer\Rfqs\Details\Delivery
{
    const FRONTEND_RESOURCE_BILLING_READ = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    const FRONTEND_RESOURCE_BILLING_UPDATE = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;
}
