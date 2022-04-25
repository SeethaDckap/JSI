<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Dealerconnect\Controller\Adminhtml;


class Context extends \Epicor\Comm\Controller\Adminhtml\Context
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTimeDateTime;


    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $backendJsHelper;

    /**
     * @var \Epicor\Comm\Helper\DataFactory
     */
    protected $commHelperFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

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

    /**
     * @var \Epicor\Dealerconnect\Model\DealergroupsFactory
     */
    protected $dealerGroupsModelFactory;

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
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTimeDateTime,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Comm\Helper\DataFactory $commHelperFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Epicor\Dealerconnect\Model\DealergroupsFactory $dealerGroupsModelFactory,
        \Epicor\Common\Model\CustomerErpaccountFactory $erpAccountFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        $canUseBaseUrl = false)
    {

        $this->dateTimeDateTime = $dateTimeDateTime;
        $this->backendJsHelper = $backendJsHelper;
        $this->commHelperFactory = $commHelperFactory;
        $this->backendSession = $backendSession;
        $this->registry = $registry;
        $this->jsonHelper = $jsonHelper;
        $this->dateTimeTimezone = $timezone;
        $this->serializer = $serializer;
        $this->dealerGroupsModelFactory = $dealerGroupsModelFactory;

        parent::__construct($request, $response, $objectManager, $eventManager, $url, $redirect, $actionFlag, $view, $messageManager, $resultRedirectFactory, $resultFactory, $session, $authorization, $auth, $helper, $backendUrl, $formKeyValidator, $localeResolver, $resultPageFactory, $registry, $resultLayoutFactory, $configData, $erpAccountFactory, $customerRepository,  $canUseBaseUrl);
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
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public function getDateTimeDateTime()
    {
        return $this->dateTimeDateTime;
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
     * @return \Magento\Backend\Model\Session
     */
    public function getBackendSession()
    {
        return $this->backendSession;
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

    /**
     * @return \Epicor\Dealerconnect\Model\DealergroupsFactory
     */
    public function getDealerModelFactory()
    {
        return $this->dealerGroupsModelFactory;
    }

}