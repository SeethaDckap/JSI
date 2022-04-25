<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account;

class Masqueradegrid extends \Epicor\SalesRep\Controller\Account
{

    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */
    protected $salesRepAccountFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $pageLayoutFactory;

    public function __construct(
        \Epicor\SalesRep\Controller\Context $context
    )
    {
        $this->registry = $context->getRegistry();
        $this->salesRepAccountFactory = $context->getSalesRepAccountFactory();

        $this->pageLayoutFactory = $context->getResultLayoutFactory();

        parent::__construct($context);
    }

    public function execute()
    {
        $customer = $this->customerSession->getCustomer();

        $account = $this->salesRepAccountFactory->create()->load($customer->getEccSalesRepAccountId());
        $this->registry->register('sales_rep_account', $account);
        $this->registry->register('sales_rep_account_base', $account);

        $resultPage = $this->pageLayoutFactory->create();

        return $resultPage;

        //$this->getResponse()->setBody($this->_view->getLayout()->createBlock('Epicor\SalesRep\Block\Manage\Select\Grid')->toHtml());
    }

}
