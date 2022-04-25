<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Block\Account\Manage;


class Menu extends \Magento\Framework\View\Element\Template
{

    private $_menuItems = array(
        '' => 'Details',
        'pricingrules' => 'Pricing Rules',
        'hierarchy' => 'Hierarchy',
        'salesreps' => 'Sales Reps',
        'erpaccounts' => 'ERP Accounts'
    );

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct(
            $context,
            $data
        );
    }


    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Epicor_SalesRep::epicor/salesrep/account/manage/menu.phtml');
    }

    public function getMenuItems()
    {

        return $this->_menuItems;
    }

    public function getLink($link)
    {
        if (!empty($link)) {
            $link = '/' . $link;
        }

        return $this->getUrl('salesrep/account_manage' . $link);
    }

    public function isCurrentPage($link)
    {
        $isPage = false;

        $action = $this->request->getActionName();

        if ($action == $link || ($action == 'index' && $link == '')) {
            $isPage = true;
        }

        return $isPage;
    }

}
