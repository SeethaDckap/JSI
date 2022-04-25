<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Epicor\BranchPickup\Helper\Data as BranchPickupHelperData;
use Epicor\BranchPickup\Helper\Branchpickup as BranchPickupHelper ;

class Select extends \Magento\Customer\Controller\Account\Index
{

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteQuoteAddressFactory;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $decoder;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;
    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerHelper;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;


    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    protected $branchPickupHelperFactory;

    protected $_helper;

    protected $_helperBranch;
    protected $locationHelper;
    private $redirectUrl;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Url\DecoderInterface $decoder,
        \Magento\Quote\Model\Quote\AddressFactory $quoteQuoteAddressFactory,
        \Epicor\BranchPickup\Helper\DataFactory $branchPickupHelperFactory,
        AccountRedirect $accountRedirect,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        SessionManagerInterface $sessionManager
    )
    {
        $this->quoteQuoteAddressFactory = $quoteQuoteAddressFactory;
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        $this->commHelper = $commHelper;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->eventManager = $context->getEventManager();
        $this->customerSession = $customerSession;
        $this->decoder = $decoder;
        $this->accountRedirect = $accountRedirect;
        $this->dealerHelper = $dealerHelper;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->branchPickupHelperFactory = $branchPickupHelperFactory;
        $this->_helper = $this->branchPickupHelperFactory->create();
        $this->_helperBranch = $branchPickupBranchpickupHelper;
        $this->locationHelper = $this->_helper->getLocationHelper();
        $this->urlBuilder = $urlBuilder;
        $this->responseFactory = $responseFactory;
        parent::__construct($context, $resultPageFactory);
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $customerSession = $this->customerSession;
            /* @var $customerSession \Magento\Customer\Model\Session */

            $customer = $customerSession->getCustomer();
            $helper = $this->commHelper;
            /* @var $helper \Epicor\Comm\Helper\Data */
            if (isset($data['id'])) {
                if ($customer->isValidErpAccount($data['id'])) {
                    $helper->startMasquerade($data['id']);
                    $this->loadDealerData($customer);
                    $helper->wipeCart();
                    $this->messageManager->getMessages(true);
                    $this->branchpickupredirect();
                    return $this->accountRedirect->getRedirect();
                } else {
                    $this->messageManager->addErrorMessage(__('You are not allowed to masquerade as this ERP Account'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('Invalid Data'));
            }
        }
        $this->_redirect($this->_url->getUrl('epicor/account/companylists'));
    }


    public function loadDealerData($customer)
    {
        //dealer toggle
        $dealerHelper = $this->dealerHelper;
        if ($customer->isDealer()) {
            $currentMode = $dealerHelper->checkCustomerLoginModeType();
            $this->customerSession->setDealerCurrentMode($currentMode);
        } else {
            $this->customerSession->unsDealerCurrentMode();
        }
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath('/');

        $sectiondata = json_decode($this->cookieManager->getCookie('section_data_ids'));

        $sectiondata->delearShopperLink += 1000;
        $sectiondata->cart += 1000;
        $sectiondata->companylink += 1000;

        $this->cookieManager->setPublicCookie(
            'section_data_ids',
            json_encode($sectiondata),
            $metadata
        );

    }

    public function branchpickupredirect()
    {
        if (!$this->isHelperSet()) {
            return false;
        }
        $this->branchForceSelect();
        $this->branchForceLogin();
    }

    private function branchForceLogin()
    {
        if (!$this->_helper->redirectToBranchpickup()) {
            return;
        }
        $this->redirectCustomerToBranchSelect();
    }

    private function redirectCustomerToBranchSelect()
    {
        if ($this->isBranchPickupAvailableAndCustomerLogged()) {
            $this->setBranchPickupRedirectUrl();
            $this->redirectToUrl();
            exit;
        }
    }

    private function redirectToUrl()
    {
        if ($this->redirectUrl) {
            $this->responseFactory->create()->setRedirect($this->redirectUrl)->sendResponse('200');
        }
    }

    private function setBranchPickupRedirectUrl()
    {
        $this->redirectUrl = $this->urlBuilder
            ->getUrl('branchpickup/pickup/select', $this->_helperBranch->issecure());
    }

    private function isBranchPickupAvailableAndCustomerLogged()
    {
        if ($this->customerSession instanceof CustomerSession) {
            return $this->_helper->isBranchPickupAvailable() && $this->customerSession->isLoggedIn();
        }

        return false;
    }

    private function isHelperSet()
    {
        return $this->_helper instanceof BranchPickupHelperData
            && $this->_helperBranch instanceof BranchPickupHelper;
    }

    private function branchForceSelect()
    {
        if ($this->isInventoryViewEnabled()) {
            $defaultLocationCode = $this->locationHelper->getDefaultLocationCode();
            $this->_helper->selectBranchPickup($defaultLocationCode, false, true);
            $this->_helperBranch->setBranchLocationFilter($defaultLocationCode);
        } else if ($this->isInventoryViewDisabled()
            && $this->_helper->getSelectedBranch()) {
            $this->_helper->emptyBranchPickup();
            $this->_helper->resetBranchLocationFilter();
        }
    }

    private function isInventoryViewEnabled()
    {
        if ($this->customerSession instanceof CustomerSession) {
            return $this->customerSession->isLoggedIn()
                && $this->locationHelper->isLocationsEnabled()
                && ($this->locationHelper->getLocationStyle() == 'inventory_view');
        }
        return false;
    }

    private function isInventoryViewDisabled()
    {
        if ($this->customerSession instanceof CustomerSession) {
            return $this->customerSession->isLoggedIn()
                && ($this->locationHelper->getLocationStyle() != 'inventory_view');
        }
        return false;
    }

}
