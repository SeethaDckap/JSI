<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Lists\Controller\Adminhtml;


class Context extends \Epicor\Comm\Controller\Adminhtml\Context
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Epicor\Lists\Model\ContractFactory
     */
    protected $listsContractFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTimeDateTime;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Epicor\Comm\Helper\DataFactory
     */
    protected $commHelperFactory;

    /**
     * @var \Epicor\Lists\Helper\DataFactory
     */
    protected $listsHelperFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;


    /**
     * @var \Epicor\Lists\Model\ListModel\RuleFactory
     */
    protected $listsListModelRuleFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     * @since 100.2.0
     */
    protected $serializer;

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
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Config\Value $configData,
        \Epicor\Lists\Model\ContractFactory $listsContractFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeDateTime,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Comm\Helper\DataFactory $commHelperFactory,
        \Epicor\Lists\Helper\DataFactory $listsHelper,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModel\RuleFactory $listsListModelRuleFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Epicor\Common\Model\CustomerErpaccountFactory $erpAccountFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        $canUseBaseUrl = false)
    {

        $this->listsContractFactory = $listsContractFactory;
        $this->dateTimeDateTime = $dateTimeDateTime;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->backendJsHelper = $backendJsHelper;
        $this->commHelperFactory = $commHelperFactory;
        $this->listsHelperFactory = $listsHelper;
        $this->backendSession = $backendSession;
        $this->registry = $registry;
        $this->listsListModelRuleFactory = $listsListModelRuleFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dateTimeTimezone = $timezone;
        $this->serializer = $serializer;

        parent::__construct($request, $response, $objectManager, $eventManager, $url, $redirect, $actionFlag, $view, $messageManager, $resultRedirectFactory, $resultFactory, $session, $authorization, $auth, $helper, $backendUrl, $formKeyValidator, $localeResolver, $resultPageFactory, $registry, $resultLayoutFactory, $configData, $erpAccountFactory, $customerRepository, $canUseBaseUrl);
    }

    /**
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function getBackendAuthSession()
    {
        return $this->backendAuthSession;
    }

    /**
     * @return \Epicor\Lists\Model\ContractFactory
     */
    public function getListsContractFactory()
    {
        return $this->listsContractFactory;
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public function getDateTimeDateTime()
    {
        return $this->dateTimeDateTime;
    }

    /**
     * @return \Epicor\Lists\Model\ListModelFactory
     */
    public function getListsListModelFactory()
    {
        return $this->listsListModelFactory;
    }

    /**
     * @return \Magento\Backend\Helper\Js
     */
    public function getBackendJsHelper()
    {
        return $this->backendJsHelper;
    }

    /**
     * @return \Epicor\Comm\Helper\DataFactory
     */
    public function getCommHelperFactory()
    {
        return $this->commHelperFactory;
    }

    /**
     * @return \Epicor\Lists\Helper\DataFactory
     */
    public function getListsHelperFactory()
    {
        return $this->listsHelperFactory;
    }

    /**
     * @return \Magento\Backend\Model\Session
     */
    public function getBackendSession()
    {
        return $this->backendSession;
    }

    /**
     * @return \Epicor\Lists\Model\ListModel\RuleFactory
     */
    public function getListsListModelRuleFactory()
    {
        return $this->listsListModelRuleFactory;
    }

    /**
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getDateTimeTimezone()
    {
        return $this->dateTimeTimezone;
    }

}