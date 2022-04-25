<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Quickstart;

class P21token extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Quickstart
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $salesOrderConfig;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $salesOrderFactory;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Common\Helper\Cart
     */
    protected $commonCartHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $salesOrderConfig,
        \Magento\Sales\Model\OrderFactory $salesOrderFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Common\Helper\Cart $commonCartHelper,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper
    ) {
        $this->customerSession = $customerSession;
        $this->salesOrderConfig = $salesOrderConfig;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->commonCartHelper = $commonCartHelper;
        $this->generic = $generic;
        $this->frameworkHelperDataHelper = $frameworkHelperDataHelper;
        $this->request = $request;
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $url = $this->getRequest()->get('url');
        $user = $this->getRequest()->get('user');
        $pass = $this->getRequest()->get('pass');

        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */

        //M1 > M2 Translation Begin (Rule p2-3)
        //Mage::app()->getResponse()->setBody($helper->getP21Token($url, $user, $pass));
        $this->_response->setBody($helper->getP21Token($url, $user, $pass));
        //M1 > M2 Translation End
    }

    }
