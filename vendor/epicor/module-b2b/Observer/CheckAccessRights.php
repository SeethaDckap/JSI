<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Observer;

class CheckAccessRights extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;



    public function __construct(
        \Epicor\B2b\Helper\Data $b2bHelper,
        \Magento\Captcha\Helper\Data $captchaHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Common\Model\ResourceModel\Access\Element\CollectionFactory $commonResourceAccessElementCollectionFactory,
        \Epicor\Common\Model\Access\ElementFactory $commonAccessElementFactory,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $state,
        \Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
        parent::__construct($b2bHelper, $captchaHelper, $customerSession, $scopeConfig, $commonAccessHelper, $frameworkHelperDataHelper, $generic, $commCustomerErpaccountFactory, $eventManager, $request, $commHelper, $storeManager, $backendJsHelper, $commonResourceAccessElementCollectionFactory, $commonAccessElementFactory, $customerUrl, $response, $urlBuilder);
    }

    /**
     * Get Captcha String
     *
     * @param \Magento\Framework\DataObject $request
     * @param string $formId
     * @return string
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $event = $observer->getEvent();

        $module = $event->getModule();
        $controller = $event->getController();
        $action = $event->getAction();

        if ($this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_portaltype', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            $helper = $this->commonAccessHelper;
            /* @var $helper \Epicor\Common\Helper\Access */

            if (!$helper->isExcludedAccess($module, $controller, $action, '', 'Access', 'portal') && !$this->request->isAjax()) {
                $customerSession = $this->customerSession;
                if (!$customerSession->isLoggedIn()) {
                    if ($action != 'noRoute' && !$this->request->isAjax()) {
                        $customerSession->setBeforeAuthUrl($this->urlBuilder->getCurrentUrl());
                    }
                    if (!$this->scopeConfig->isSetFlag('epicor_b2b/registration/reg_portaltype', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                        $this->messageManager->addErrorMessage(__('You do not have sufficient privileges to access the requested page'));
                    }
                    //M1 > M2 Translation Begin (Rule p2-3)
                    //Mage::app()->getResponse()->setRedirect(Mage::getUrl('b2b/portal/login', array('access' => 'denied')), 403);
                    //die(Mage::app()->getResponse());
                    //M1 > M2 Translation Begin (Rule p2-4)
                    //$response = $this->response->setRedirect(Mage::getUrl('b2b/portal/login', array('access' => 'denied')), 403)->sendResponse();
                     $this->response->setRedirect($this->urlBuilder->getUrl('b2b/portal/login', array('access' => 'denied')), 403);
                    //M1 > M2 Translation End
                    die($this->response->sendResponse());
                    //M1 > M2 Translation End
                } else {
                    $this->checkStoreSelector($module, $controller, $action);
                }
            } else {
                if ($module == 'Epicor_Common' && $controller == 'Account' && $action == 'login') {
                    //M1 > M2 Translation Begin (Rule p2-3)
                    /*Mage::app()->getResponse()->setRedirect(Mage::getUrl('b2b/portal/login'));
                    die(Mage::app()->getResponse());*/
                    //M1 > M2 Translation Begin (Rule p2-4)
                    //$response = $this->response->setRedirect(Mage::getUrl('b2b/portal/login'))->sendResponse();
                    $response = $this->response->setRedirect($this->urlBuilder->getUrl('b2b/portal/login'))->sendResponse();
                    //M1 > M2 Translation End
                    die($response);
                    //M1 > M2 Translation End
                }
            }
        } else {
            $defaultErpAccountCode = $this->scopeConfig->getValue('customer/create_account/default_erpaccount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            // check erp code still exists on system, before calling store selector check
            $erp = $this->commCustomerErpaccountFactory->create()->load($defaultErpAccountCode);

            if (!$erp->isObjectNew()) {
                $this->checkStoreSelector($module, $controller, $action);
            }
            if ($module == 'Epicor_Common' && $controller == 'Account' && $action == 'login') {
                //M1 > M2 Translation Begin (Rule p2-3)
                /*Mage::app()->getResponse()->setRedirect(Mage::getUrl('b2b/portal/login'));
                die(Mage::app()->getResponse());*/
                //M1 > M2 Translation Begin (Rule p2-4)
                //$response = $this->response->setRedirect(Mage::getUrl('b2b/portal/login'))->sendResponse();
                $response = $this->response->setRedirect($this->urlBuilder->getUrl('b2b/portal/login', $this->request->getParams()))->sendResponse();
                //M1 > M2 Translation End
                die($response);
                //M1 > M2 Translation End
            }
        }

        $this->eventManager->dispatch('epicor_btob_check_access_rights_after', array(
            'module' => $module,
            'controller' => $controller,
            'action' => $action,
            'controller_action' => $event->getControllerAction(),
            'request' => $this->request
        ));
    }


    public function checkStoreSelector($module, $controller, $action)
    {
        if ($module != 'Epicor_ErpSimulator' && !($module == 'Epicor_Comm' && $controller == 'Data')
            && !($module == 'Epicor_Comm' && $controller == 'Message')
            && ($controller != 'store' && $action != 'selector')
            && ($controller != 'store' && $action != 'select')
            && ($controller != 'file' && $action != 'request')
        ) {
            $helper = $this->commHelper;
            $storeSelectorEnabled = $this->scopeConfig->isSetFlag('Epicor_Comm/brands/show_store_selector', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if ($storeSelectorEnabled) {
                /* @var $helper \Epicor\Comm\Helper\Data */

                //--SF   $stores = $helper->getBrandSelectStores();
                $stores = $this->checkAvailableStoresForWebsite();

                $redirect = false;

                if ($action == 'loginPost') {
                    $store = $this->storeManager->getStore();

                    if (!in_array($store->getId(), array_keys($stores))) {
                        $redirect = true;
                    }
                } else {
                    if (!$this->customerSession->getHasStoreSelected()) {
                        $redirect = true;
                    }
                }

                if ($redirect) {
                    $this->customerSession->setHasStoreSelected(false);
                    $this->response->setRedirect($this->urlBuilder->getUrl('epicor_comm/store/selector'));
                    //M1 > M2 Translation Begin (Rule 14)
                    //die(Mage::app()->getResponse());
                    die($this->response->sendResponse());
                    //M1 > M2 Translation End
                } else {
                    $this->customerSession->setHasStoreSelected(true);
                }
                $helper->checkForceMasqurading();
            }
        }
    }

    protected function checkAvailableStoresForWebsite()
    {
        $helper = $this->commHelper;
        $stores = $helper->getSelectableStores();
        if (!$stores) {
            $this->messageManager->addErrorMessage('No Stores available for this user on this site, unable to log in');
            $url = $this->customerUrl->getLogoutUrl();
            $this->response->setRedirect($url);
        }
        return $stores;
    }
}
