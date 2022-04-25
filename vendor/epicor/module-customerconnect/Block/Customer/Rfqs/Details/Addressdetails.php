<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details;


/**
 * RFQ address details display 
 * 
 * Loaded by ajax to update addresses when address changed in dropdown
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Addressdetails extends \Epicor\Customerconnect\Block\Customer\Address
{

    const FRONTEND_RESOURCE_BILLING_READ = \Epicor\AccessRight\Acl\RootResource::FRONTEND_RESOURCE;

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_Customerconnect::customerconnect/customer/account/rfqs/details/address_details.phtml');
    }

    public function showName()
    {
        return false;
    }

}
