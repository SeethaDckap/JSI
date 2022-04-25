<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Lists;

class Selectaddressajax extends \Epicor\Lists\Controller\Lists
{
    /**
     * Address Select Ajax Action
     */
    public function execute()
    {
        $addressId = $this->getRequest()->getParam('addressid');
        if ($addressId) {
            $listHelper = $this->listsFrontendRestrictedHelper;
            /* @var $listHelper Epicor_Lists_Helper_Frontend_Restricted */
            $listHelper->setRestrictionAddress($addressId);
            $helper = $this->listsHelper;
            $resetBranchPickup = $helper->resetLocationFilter();
        }
    }

}
