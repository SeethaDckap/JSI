<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep;

class Erpaccountsgrid extends \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    public function __construct(\Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Context $context)
    {
        $this->resultLayoutFactory = $context->getResultLayoutFactory();
        parent::__construct($context);
    }


    public function execute()
    {
        $this->_initSalesRepAccount();
        $customers = $this->getRequest()->getParam('erpaccounts');

        $resultPage = $this->resultLayoutFactory->create();

        $this->_view->getLayout()->getBlock('erpaccount_grid')
            ->setSelected($customers);

        return $resultPage;
    }

}
