<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Observer;

class SendAst  implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\Comm\Model\Message\Request\AstFactory
     */
    protected $commMessageRequestAstFactory;

    protected $customerCustomerFactory;

    protected $customer;

    protected $customerSession;

    protected $commHelper;

    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    public function __construct(
        \Epicor\Comm\Model\Message\Request\AstFactory $commMessageRequestAstFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customer,
        \Epicor\Comm\Helper\DataFactory $commHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseInterface $response
    ){
        $this->commMessageRequestAstFactory = $commMessageRequestAstFactory;
        $this->scopeConfig = $scopeConfig;
        $this->customer = $customer;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
        $this->commHelper = $commHelper;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->response = $response;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfig->getValue('epicor_comm_enabled_messages/ast_request/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $ast = $this->commMessageRequestAstFactory->create();
            $customerLoggedIn = $this->customerSession->isLoggedIn();
            if($customerLoggedIn) {
                $customerId =$this->customerSession->getCustomerId();
                $customer = $this->customer->load($customerId);
                $customerERPData = $customer->getCustomerErpAccount();
                $root = true;
                if (
                    !$customerERPData ||
                    $customerERPData->getId() == $this->scopeConfig->getValue('customer/create_account/default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ||
                    !$customerERPData->checkCustomertype()
                ) {
                    $root = false;
                }
                if($root) {
                    $ast->setCustomerGroupId($customerERPData->getId());
                    $ast->sendMessage();

                    $helper = $this->commHelper->create();

                    if (!$helper->canCustomerAccessUrl($this->urlBuilder->getCurrentUrl()) && !$this->checksectionUrl()) {
                        $allowUrl= true;
                        if($customer->isSupplier()) {
                            $allowUrl=false;
                        }
                        $route = 'customer/account';
                        if($allowUrl) {
                            $this->messageManager->addErrorMessage(__('You do not have sufficient privileges to access the requested page'));
                            $this->response->setRedirect($this->urlBuilder->getUrl($route, array('access' => 'denied')), 403);
                            die($this->response->sendResponse());
                        }
                    }

                }
            }

        }
    }

    public function checksectionUrl()
    {
        if($this->request->getModuleName()=='customer' && $this->request->getControllerName() =='section'){
            return true;
        }
        return false;
    }
}