<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\MessageInterface;

class CheckCommRights implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\DataFactory
     */
    private $commHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Epicor\Common\Model\Url
     */
    private $url;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;


    /**
     * CheckCommRights constructor.
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Epicor\Comm\Helper\DataFactory $commHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Epicor\Common\Model\Url $url
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Comm\Helper\DataFactory $commHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Epicor\Common\Model\Url $url
    )
    {
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->commHelper = $commHelper;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->response = $response;
        $this->urlBuilder = $urlBuilder;
        $this->url = $url;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $restriction = $this->scopeConfig->getValue(
            'customer/onstop/restriction',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        /* @var $helper \Epicor\Comm\Helper\Data */
        $helper = $this->commHelper->create();
        $this->registry = $helper->getRegistry();
        $customerSession = $this->customerSessionFactory->create();
        /* @var $customer \Epicor\Comm\Model\Customer */
        $customer = $customerSession->getCustomer();

        if ($customerSession->isLoggedIn() && $restriction == 'login') {
            if (!$customer->isSupplier()) {
                $erpAccount = $helper->getErpAccountInfo();
                /* @var $erpAccount \Epicor\Comm\Model\Customer\Erpaccount */
                if ($erpAccount && !$erpAccount->isObjectNew()
                    && $erpAccount->getOnstop($this->storeManager->getStore()
                        ->getBaseCurrencyCode())
                ) {
                    $customerSession->logout();
                    $this->messageManager->addErrorMessage(__('Your account is now no onstop and has no access'));
                    $this->response->setRedirect(
                        $this->urlBuilder->getUrl(
                            'customer/account/login',
                            array('access' => 'denied')
                        ),
                        403
                    );
                    die($this->response->sendResponse());
                }
            }
        }

        if (!$helper->canCustomerAccessUrl($this->urlBuilder->getCurrentUrl())
            && !$this->checkSectionUrl()
        ) {
            $allowUrl = true;
            $route = $customerSession->isLoggedIn() ? 'customer/account' : '';
            $accountUrl = $this->checkAccountUrl();
            if ($customer->isSupplier() && $accountUrl) {
                $allowUrl = false;
            }
            if ($allowUrl) {
                $this->messageManager->addErrorMessage(__('You do not have sufficient privileges to access the requested page'));
                $this->response->setRedirect(
                    $this->urlBuilder->getUrl($route, array('access' => 'denied')),
                    403
                );
                die($this->response->sendResponse());
            }
        }

        $isOnStopNotification = $this->scopeConfig->isSetFlag(
            'customer/onstop/notification',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($restriction != 'none' && $isOnStopNotification) {
            if (!$customer->isSupplier()
                && !$this->request->isPost()
                && !$this->checkSectionUrl()
            ) {
                $erpAccount = $helper->getErpAccountInfo();
                /* @var $erpAccount Epicor_Comm_Model_Customer_Erpaccount */
                if ($erpAccount
                    && !$erpAccount->isObjectNew()
                    && $erpAccount->getOnstop($this->storeManager->getStore()
                        ->getBaseCurrencyCode())
                ) {
                    if ($restriction == 'cart_checkout') {
                        $error = __(
                            'Your account is onstop, you will not be able to add items to the cart / proceed to checkout'
                        );
                    } elseif ($restriction == 'checkout') {
                        $error = __(
                            'Your account is onstop, you will not be able to proceed to checkout'
                        );
                    }

                    if (!$helper->warningExists($error)) {
                        $this->messageManager->addWarningMessage($error);
                    }
                }
            }
        }

        if ($helper->isMasquerading()) {
            /* @var $request \Magento\Framework\App\Request\Http */
            $request = $observer->getEvent()->getRequest();
            $action = $observer->getEvent()->getAction();
            $currentUrl = $this->urlBuilder->getCurrentUrl();
            $path = $this->url->parseUrl($currentUrl)->getPath();
            $erpAccount = $helper->getErpAccountInfo();
            $error = __('You are Masquerading as %1', $erpAccount->getName());

            if (
            $this->scopeConfig->isSetFlag(
                'epicor_comm_erp_accounts/masquerade/show_message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ) {
                $showMessage = true;
                if ($request->isPost()
                    || $request->isAjax()
                    || $action == 'logout'
                    || $action == 'masquerade'
                    || strpos($path, 'ewacss') !== false
                    || $helper->errorExists($error)
                    || strpos($path, 'ewaedit') !== false
                    || strpos($path, 'comm/message/msq') !== false
                    || $customerSession->getIsPunchout()
                    || $this->registry->registry('set_masquerade')
                ) {
                    $showMessage = false;
                }

                if ($showMessage) {
                    $showMessage =$this->checkMessageCookies($error);
                }
                if ($showMessage) {

                    $message = $this->messageManager
                        ->createMessage(MessageInterface::TYPE_SUCCESS)
                        ->setText($error);
                    $this->messageManager->addUniqueMessages([$message]);
                    $this->registry->unregister('set_masquerade');
                    $this->registry->register('set_masquerade', 1);
                }
            }
        }
    }


    /**
     * Check message cookies
     *
     * @param string $error
     * @return boolean
     */
    protected function checkMessageCookies($error)
    {
        $messageCookies = !empty($_COOKIE['mage-messages']) ? $_COOKIE['mage-messages'] :'';
        if ($messageCookies) {
            $messageCookies = json_decode($messageCookies, true);
            foreach ($messageCookies as $messageCookie) {
                if (isset($messageCookie['text']) && $error == $messageCookie['text']) {
                    return false;
                }
            }
        }
        return true;
    }

    public function checkSectionUrl()
    {
        if ($this->request->getModuleName() == 'customer'
            && $this->request->getControllerName() == 'section'
        ) {
            return true;
        }

        return false;
    }

    public function checkAccountUrl()
    {
        if ($this->request->getModuleName() == 'customer'
            && $this->request->getControllerName() == 'account'
        ) {
            return true;
        }

        return false;
    }

}