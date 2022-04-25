<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\B2b\Observer;

use Epicor\B2b\Helper\Data as B2bHelper;
use Epicor\Comm\Helper\Data as CommHelper;
use Epicor\Comm\Model\Customer\ErpaccountFactory;
use Epicor\Common\Helper\Access;
use Epicor\Common\Model\Access\ElementFactory;
use Epicor\Common\Model\ResourceModel\Access\Element\CollectionFactory as CommonAccessElementFactory;
use Magento\Backend\Helper\Js as BackendJsHelper;
use Magento\Captcha\Helper\Data as CaptchaHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Session\Generic as GenericSession;
use Magento\Framework\Session\SessionManagerInterface as SessionManager;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class CustomerAccountCreated extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    private $sessionManager;

    public function __construct(
        SessionManager $sessionManager,
        B2bHelper $b2bHelper,
        CaptchaHelper $captchaHelper,
        CustomerSession $customerSession,
        ScopeConfigInterface $scopeConfig,
        Access $commonAccessHelper,
        UrlHelper $frameworkHelperDataHelper,
        GenericSession $generic,
        ErpaccountFactory $commCustomerErpAccountFactory,
        ManagerInterface $eventManager,
        Http $request,
        CommHelper $commHelper,
        StoreManagerInterface $storeManager,
        BackendJsHelper $backendJsHelper,
        CommonAccessElementFactory $commonResourceAccessElementCollectionFactory,
        ElementFactory $commonAccessElementFactory,
        CustomerUrl $customerUrl,
        ResponseInterface $response,
        UrlInterface $urlBuilder
    ) {
        parent::__construct(
            $b2bHelper,
            $captchaHelper,
            $customerSession,
            $scopeConfig,
            $commonAccessHelper,
            $frameworkHelperDataHelper,
            $generic,
            $commCustomerErpAccountFactory,
            $eventManager,
            $request,
            $commHelper,
            $storeManager,
            $backendJsHelper,
            $commonResourceAccessElementCollectionFactory,
            $commonAccessElementFactory,
            $customerUrl,
            $response,
            $urlBuilder
        );
        $this->sessionManager = $sessionManager;
    }

    public function execute(Observer $observer)
    {
        $this->sessionManager->setCustomerIsNew(true);

        return $observer;
    }
}
