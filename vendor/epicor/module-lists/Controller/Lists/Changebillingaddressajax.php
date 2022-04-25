<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class Changebillingaddressajax extends \Epicor\Lists\Controller\Lists
{

    public function execute()
    {
        $frontendHelper = $this->listsFrontendRestrictedHelper;
        /* @var $frontendHelper Epicor_Lists_Helper_Frontend_Restricted */
        $addressId = $this->getRequest()->getParam('addressid');
        $removeProducts = $frontendHelper->checkProductAddressNew($addressId, 'billing');
        $this->sendAjaxResponse($removeProducts, $addressId);
    }

}
