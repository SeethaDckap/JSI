<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account;

class Index extends \Epicor\SalesRep\Controller\Account
{
    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */
    protected $salesRepAccountFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(\Epicor\SalesRep\Controller\Context $context)
    {
        $this->registry = $context->getRegistry();
        $this->salesRepAccountFactory = $context->getSalesRepAccountFactory();
        $this->resultPageFactory = $context->getResultPageFactory();

        parent::__construct($context);
    }

    /**
     * Index action
     */
    public function execute()
    {
        $customer = $this->customerSession->getCustomer();
        $account = $this->salesRepAccountFactory->create()->load($customer->getEccSalesRepAccountId());

        $this->registry->register('sales_rep_account', $account);
        $this->registry->register('sales_rep_account_base', $account);


        $result = $this->resultPageFactory->create();

        return $result;
    }

}
