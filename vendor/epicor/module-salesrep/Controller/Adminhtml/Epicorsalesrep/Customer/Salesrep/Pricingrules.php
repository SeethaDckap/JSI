<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep;

class Pricingrules extends \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep
{

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerResourceModelCustomerCollectionFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */
    protected $salesRepAccountFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    public function __construct(
        \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Context $context
    ) {
        $this->backendAuthSession = $context->getBackendAuthSession();
        $this->backendJsHelper = $context->getBackendJsHelper();
        $this->customerResourceModelCustomerCollectionFactory = $context->getCustomerResourceModelCustomerCollectionFactory();
        $this->customerCustomerFactory = $context->getCustomerCustomerFactory();
        $this->salesRepAccountFactory = $context->getSalesRepAccountFactory();
        $this->backendSession = $context->getSession();
        $this->resultLayoutFactory = $context->getResultLayoutFactory();
        $this->registry = $context->getRegistry();
        parent::__construct(
            $context
        );
    }


public function execute()
    {
        $this->_initSalesRepAccount();
        
         $resultPage = $this->resultLayoutFactory->create();

        $this->_view->getLayout()->getBlock('pricingrule_grid')
            ->setSelected($this->getRequest()->getPost('salesreps', null));

        return $resultPage;
        
        
//        $this->loadLayout();
//        $this->renderLayout();
    }

    }
