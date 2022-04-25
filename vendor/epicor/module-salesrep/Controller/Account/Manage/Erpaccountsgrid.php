<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class Erpaccountsgrid extends \Epicor\SalesRep\Controller\Account\Manage
{

    protected $resultLayoutFactory;

    public function __construct(\Epicor\SalesRep\Controller\Context $context)
    {
        $this->resultLayoutFactory = $context->getResultLayoutFactory();
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultLayoutFactory->create();

        $customers = $this->getRequest()->getParam('erpaccounts');
        $this->_view->getLayout()->getBlock('manage.erpaccounts')->setSelected($customers);

        return $result;
    }

}
