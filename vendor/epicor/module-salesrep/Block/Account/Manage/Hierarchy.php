<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage;


/**
 * Sales Rep Account ERP Account List
 * 
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 */
class Hierarchy extends \Epicor\SalesRep\Block\Account\Manage\AbstractBlock
{

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_SalesRep::epicor/salesrep/account/manage/hierarchy.phtml');
    }

    public function canAddChildrenAccounts()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Account\Manage */
        return $helper->canAddChildrenAccounts();
    }

}
