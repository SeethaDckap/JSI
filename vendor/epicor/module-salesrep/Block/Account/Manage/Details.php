<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage;


class Details extends \Epicor\SalesRep\Block\Account\Manage\AbstractBlock
{

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_SalesRep::epicor/salesrep/account/manage/details.phtml');
        $this->setTitle(__('Details'));
    }

    public function getSalesRepAccount()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        return $helper->getManagedSalesRepAccount();
    }

    public function canEdit()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */

        return $helper->canEdit();
    }

}
