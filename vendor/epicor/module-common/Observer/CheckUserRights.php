<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Observer;

use Epicor\Comm\Model\Customer\Erpaccount\AddressFactory;
use Epicor\Common\Helper\AccessFactory;
use Epicor\Common\Helper\DataFactory;
use Epicor\Common\Model\Access\Group\CustomerFactory;
use Epicor\Lists\Helper\Frontend\ProductFactory as ListProductFactory;
use Magento\Backend\Helper\Js;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Session\Generic;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Epicor\SalesRep\Model\ResourceModel\Account\Collection;

/**
 * Class CheckUserRights
 * @package Epicor\Common\Observer
 */
class CheckUserRights extends AbstractObserver implements ObserverInterface
{
    /**
     * Configuration path for Sales Rep browse catalog
     */
    const XML_PATH_SALESREP_CATALOG_ALLOWED = 'epicor_salesrep/general/catalog_allowed';

    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var Collection
     */
    private $salesRepAccount;

    /**
     * CheckUserRights constructor.
     * @param Http $request
     * @param AccessFactory $commonAccessHelper
     * @param SessionFactory $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param Data $frameworkHelperDataHelper
     * @param ManagerInterface $eventManager
     * @param Generic $generic
     * @param Js $backendJsHelper
     * @param CustomerFactory $commonAccessGroupCustomerFactory
     * @param Registry $registry
     * @param DataFactory $commonHelper
     * @param Session $backendAuthSession
     * @param AddressFactory $commCustomerErpaccountAddressFactory
     * @param ProductFactory $catalogProductFactory
     * @param ListProductFactory $listsFrontendProductHelper
     * @param CategoryFactory $catalogCategoryFactory
     * @param Header $header
     * @param UrlInterface $url
     * @param ResponseInterface $response
     * @param MessageManagerInterface $messageManager
     * @param Url $customerUrl
     * @param Collection $salesRepAccount
     */
    public function __construct(
        Http $request,
        AccessFactory $commonAccessHelper,
        SessionFactory $customerSession,
        ScopeConfigInterface $scopeConfig,
        Data $frameworkHelperDataHelper,
        ManagerInterface $eventManager,
        Generic $generic,
        Js $backendJsHelper,
        CustomerFactory $commonAccessGroupCustomerFactory,
        Registry $registry,
        DataFactory $commonHelper,
        Session $backendAuthSession,
        AddressFactory $commCustomerErpaccountAddressFactory,
        ProductFactory $catalogProductFactory,
        ListProductFactory $listsFrontendProductHelper,
        CategoryFactory $catalogCategoryFactory,
        Header $header,
        UrlInterface $url,
        ResponseInterface $response,
        MessageManagerInterface $messageManager,
        Url $customerUrl,
        Collection $salesRepAccount
    ) {
        $this->messageManager = $messageManager;
        $this->customerUrl = $customerUrl;
        parent::__construct($request, $commonAccessHelper, $customerSession, $scopeConfig, $frameworkHelperDataHelper, $eventManager, $generic, $backendJsHelper, $commonAccessGroupCustomerFactory, $registry, $commonHelper, $backendAuthSession, $commCustomerErpaccountAddressFactory, $catalogProductFactory, $listsFrontendProductHelper, $catalogCategoryFactory, $header, $url, $response);
        $this->salesRepAccount = $salesRepAccount;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $current_route = $this->request->getModuleName() . '/' . $this->request->getControllerName() . '/' . $this->request->getActionName();
        $redirectUrl = $this->url->getUrl('customer/account');

        if (strpos($current_route, 'createpassword') !== false) {
            return;
        }
        
        $module = $this->request->getControllerModule();
        $controller = ucfirst($this->request->getControllerName());
        $action = $this->request->getActionName();
        $controllerAction = $observer->getControllerAction();

        /* @var $helper \Epicor\Common\Helper\Access */
        $helper = $this->commonAccessHelper->create();

        /* @var $customerSession \Magento\Customer\Model\Session */
        $customerSession = $this->customerSession->create();

        if ($customerSession->isLoggedIn()) {
            // check if customer is valid for this store

            /* @var $customer \Epicor\Comm\Model\Customer */
            $customer = $customerSession->getCustomer();

            //Multi Erp Account
            $multierpallowedUrl = [
                'epicor/account/companylists',
                'epicor/account/select',
                'epicor/account/favourite',
                'customer/section/load',
                'customer/account/logout',
                'punchout/setuprequest/index'
            ];
            if ($customer && !in_array($current_route, $multierpallowedUrl)) {
                /* @var $customer \Epicor\Comm\Model\Customer */
                $getErpAcctCounts = $customer->getErpAcctCounts();
                if (!$customerSession->getMasqueradeAccountId() &&
                    (is_array($getErpAcctCounts) && count($getErpAcctCounts) > 1)) {
                    $helper->wipeCart();
                    $this->messageManager->addWarningMessage(__('Please Select a Company'));
                    $redirectUrl = $this->url->getUrl('epicor/account/companylists');
                    $this->response->setRedirect($redirectUrl);
                    die($this->response->sendResponse());
                    return;
                }
            }

            $error = __('You are no longer able to access this store');

            if (!$customer->isSupplier()) {
                $valid = $customer->isValidForStore();
            } else {
                $valid = $customer->isValidForStore(null, 'supplier');
            }

            if ($valid) {
                if ($customer->isSupplier() && !$helper->isLicensedFor(array('Supplier')) || ($customer->isGuest() && !$helper->isLicensedFor(array('Consumer'))) || ($customer->isCustomer() && !$helper->isLicensedFor(array('Customer')))) {
                    $valid = false;
                }
            }

            if ($valid) {
                if ($this->scopeConfig->isSetFlag('epicor_common/accessrights/active', ScopeInterface::SCOPE_STORE)) {
                    $groups = $helper->getSessionAccessGroups();
                    if (empty($groups)) {
                        $error = __('Invalid login or password (006)');
                        $valid = false;
                    }
                }
            }

            if (!$valid) {
                $customerSession->clearStorage();
                $this->messageManager->addErrorMessage($error);
                $this->response->setRedirect($this->url->getUrl('customer/account/login', array('access' => 'denied'), 403));
                die($this->response->sendResponse());
            }
        } else {
            if (strpos($current_route, 'login') === false
                && ((!$this->request->isAjax() && !$this->request->isPost())
                    || $this->request->getParam('allow_url'))
            ) {
                $customerSession->setBeforeAuthUrl($this->url->getCurrentUrl());
            }
        }

        $this->eventManager->dispatch('epicor_common_check_user_rights_before', array(
            'module' => $module,
            'controller' => $controller,
            'action' => $action,
            'controller_action' => $controllerAction,
            'request' => $this->request
        ));

        if (!$helper->canAccessUrl(ltrim($this->request->getPathInfo(), '/'), true)) {
            if ($customerSession->isLoggedIn()) {
                if ($customer->isSupplier()) {
                    $redirectUrl = $this->url->getUrl('supplierconnect/account');
                } else if ($customer->isSalesRep() &&
                     (!$customerSession->getMasqueradeAccountId())
                ) {
                    $canBrowseCatalog = $this->scopeConfig->getValue(self::XML_PATH_SALESREP_CATALOG_ALLOWED, ScopeInterface::SCOPE_STORE);
                    if ($canBrowseCatalog == 'forceN') {
                        $redirectUrl = $this->url->getUrl('salesrep/account');
                    } else if ($canBrowseCatalog == 'N' || $canBrowseCatalog == 'Y') {
                        $salesRepAcc = $this->salesRepAccount
                            ->addFieldToFilter('id', $customer->getEccSalesRepAccountId())
                            ->getFirstItem();
                        if ($salesRepAcc->getCatalogAccess() != 'Y') {
                            $redirectUrl = $this->url->getUrl('salesrep/account');
                        }
                    }
                } else {
                    $redirectUrl = $this->url->getUrl('customer/account');
                }
            } else {
                $customerSession->setBeforeAuthUrl($this->url->getCurrentUrl());
                $redirectUrl = $this->url->getUrl('customer/account/login');
            }

            $this->response->setRedirect($redirectUrl, 403);
            die($this->response->sendResponse());
        }

        $allowed = $helper->customerHasAccess($module, $controller, $action, '', 'Access');

        if (!$allowed) {

            $logged_in = $customerSession->isLoggedIn();
            if ($controllerAction instanceof \Magento\Cms\Controller\Index\Index) {
                $route = $logged_in ? 'customer/account' : 'customer/account/login';
            } else {
                $route = $logged_in ? 'customer/account' : '';
            }

            if (!$logged_in && !$this->scopeConfig->isSetFlag('customer/startup/redirect_dashboard', ScopeInterface::SCOPE_STORE)) {
                $customerSession->setBeforeAuthUrl($this->url->getCurrentUrl());
                $customerSession->setBeforeAuthUrl($this->customerUrl->getAccountUrl());
            }

            $this->messageManager->addErrorMessage(__('You do not have sufficient privileges to access the requested page'));
            $rejectedUrl = $this->header->getHttpReferer() ? $this->header->getHttpReferer() : $this->url->getUrl($route, array('access' => 'denied'));
            $this->response->setRedirect($rejectedUrl, 403);
            die($this->response->sendResponse());
        }
    }

}