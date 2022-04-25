<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep;

class Pricingrulesgrid extends  \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(\Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Context $context)
    {
        $this->resultPageFactory = $context->getResultPageFactory();

        parent::__construct($context);
    }


    public function execute()
    {
        $this->_initSalesRepAccount();

        $resultPage = $this->resultPageFactory->create();

        return $resultPage;
    }

    }
