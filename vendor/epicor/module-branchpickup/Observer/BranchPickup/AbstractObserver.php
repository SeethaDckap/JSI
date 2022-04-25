<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

use Magento\Framework\Event\Observer;

class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    protected $_helper;

    protected $_helperBranch;

    protected $_branchModel;

    protected $branchPickupHelperFactory;

    protected $branchPickupBranchpickupFactory;

    protected $customerSession;

    protected $branchPickupSessionHelper;

    protected $salesRepHelper;

    protected $customerCustomerFactory;

    protected $commHelper;

    protected $generic;

    /**
     * @var \Epicor\BranchPickup\Helper\Branchpickup
     */
    protected $branchPickupBranchpickupHelper;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    
    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $responseFactory;    

    public function __construct(
        \Epicor\BranchPickup\Helper\DataFactory $branchPickupHelperFactory,
        \Epicor\BranchPickup\Model\BranchpickupFactory $branchPickupBranchpickupFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\BranchPickup\Helper\Session $branchPickupSessionHelper,
        \Epicor\SalesRep\Helper\Data $salesRepHelper,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper
    )
    {
        $this->branchPickupHelperFactory = $branchPickupHelperFactory;
        $this->branchPickupBranchpickupFactory = $branchPickupBranchpickupFactory;
        $this->customerSession = $customerSession;
        $this->branchPickupSessionHelper = $branchPickupSessionHelper;
        $this->salesRepHelper = $salesRepHelper;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->commHelper = $commHelper;
        $this->generic = $generic;
        $this->_helper = $this->branchPickupHelperFactory->create();
        $this->responseFactory = $responseFactory;
        $this->_helperBranch = $branchPickupBranchpickupHelper;
        $this->_branchModel = $this->branchPickupBranchpickupFactory->create();
        $this->response = $response;
        $this->urlBuilder = $urlBuilder;
    }




    /* Set Shipping Session */


    /* Hide Location picker If branchpick was enabled for a customer */


    /* Generate BSV Request (Orderfor/Orderby/storecollect) */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    }




}

