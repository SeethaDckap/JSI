<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml;


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
     * @var \Magento\Framework\App\Config\Value
     */
    protected $configData;
    
    /**
     * 
     *   @var \Magento\Framework\Event\ManagerInterface $eventManager,
     */
    protected $eventManager;

    /**
     * @var \Epicor\Common\Model\CustomerErpaccountFactory
     */
    protected $erpAccountFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

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
        \Magento\Framework\App\Config\Value $configData,
        \Epicor\Common\Model\CustomerErpaccountFactory $erpAccountFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        $canUseBaseUrl = false
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->configData = $configData;
        $this->eventManager = $eventManager;
        $this->erpAccountFactory = $erpAccountFactory;
        $this->customerRepository = $customerRepository;
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
     * @return \Magento\Framework\App\Config\Value
     */
    public function getConfigData()
    {
        return $this->configData;
    }
    /**
     * @return \Magento\Framework\App\Config\Value
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    public function getErpAcctFactory()
    {
        return $this->erpAccountFactory;
    }

    public function getCustomerRepository()
    {
        return $this->customerRepository;
    }
}
