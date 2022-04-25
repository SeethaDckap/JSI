<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer;


class Context extends \Magento\Backend\App\Action\Context
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

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
     * @var \Epicor\SalesRep\Model\Pricing\RuleFactory
     */
    protected $salesRepPricingRuleFactory;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\Helper\Data $helper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerResourceModelCustomerCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\SalesRep\Model\AccountFactory $salesRepAccountFactory,
        \Epicor\SalesRep\Model\Pricing\RuleFactory $salesRepPricingRuleFactory,
        \Magento\Backend\Helper\Js $backendJsHelper,

        $canUseBaseUrl = false)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->resultLayoutFactory = $resultLayoutFactory;

        $this->backendAuthSession = $backendAuthSession;
        $this->customerResourceModelCustomerCollectionFactory = $customerResourceModelCustomerCollectionFactory;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->salesRepAccountFactory = $salesRepAccountFactory;
        $this->salesRepPricingRuleFactory = $salesRepPricingRuleFactory;
        $this->backendJsHelper = $backendJsHelper;


        parent::__construct($request, $response, $objectManager, $eventManager, $url, $redirect, $actionFlag, $view, $messageManager, $resultRedirectFactory, $resultFactory, $session, $authorization, $auth, $helper, $backendUrl, $formKeyValidator, $localeResolver, $canUseBaseUrl);
    }


    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function getResultPageFactory()
    {
        return $this->resultPageFactory;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Magento\Framework\View\Result\LayoutFactory
     */
    public function getResultLayoutFactory()
    {
        return $this->resultLayoutFactory;
    }

    /**
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function getBackendAuthSession()
    {
        return $this->backendAuthSession;
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    public function getCustomerResourceModelCustomerCollectionFactory()
    {
        return $this->customerResourceModelCustomerCollectionFactory;
    }

    /**
     * @return \Magento\Customer\Model\CustomerFactory
     */
    public function getCustomerCustomerFactory()
    {
        return $this->customerCustomerFactory;
    }

    /**
     * @return \Epicor\SalesRep\Model\AccountFactory
     */
    public function getSalesRepAccountFactory()
    {
        return $this->salesRepAccountFactory;
    }

    /**
     * @return \Epicor\SalesRep\Model\Pricing\RuleFactory
     */
    public function getSalesRepPricingRuleFactory()
    {
        return $this->salesRepPricingRuleFactory;
    }

    /**
     * @return \Magento\Backend\Helper\Js
     */
    public function getBackendJsHelper()
    {
        return $this->backendJsHelper;
    }


}