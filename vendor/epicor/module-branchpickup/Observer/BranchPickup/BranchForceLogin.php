<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Observer\BranchPickup;

use \Magento\Framework\Session\SessionManagerInterface as SessionManager;
use Epicor\BranchPickup\Helper\Data as BranchPickupHelperData;
use Epicor\BranchPickup\Helper\Branchpickup as BranchPickupHelper ;
use Epicor\BranchPickup\Helper\DataFactory as BranchPickupHelperFactory;
use Epicor\BranchPickup\Helper\Session as BranchPickupHelperSession;
use Epicor\BranchPickup\Model\BranchpickupFactory;
use Epicor\Comm\Helper\Data;
use Epicor\SalesRep\Helper\Data as SalesRepHelper;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\UrlInterface;
use Epicor\Lists\Helper\Frontend\Restricted as ListRestriction;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Checkout\Helper\Cart as CartHelper;

class BranchForceLogin extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    private $customer;
    private $redirectUrl;
    protected $locationHelper;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Restricted
     */
    protected $listsFrontendRestrictedHelper;

    /**
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var SessionManager
     */
    private $sessionManager;
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * BranchForceLogin constructor.
     * @param SessionManager $sessionManager
     * @param BranchPickupHelperFactory $branchPickupHelperFactory
     * @param BranchpickupFactory $branchPickupBranchPickupFactory
     * @param CustomerSession $customerSession
     * @param BranchPickupHelperSession $branchPickupSessionHelper
     * @param SalesRepHelper $salesRepHelper
     * @param CustomerFactory $customerCustomerFactory
     * @param Data $commHelper
     * @param Generic $generic
     * @param ResponseInterface $response
     * @param UrlInterface $urlBuilder
     * @param ResponseFactory $responseFactory
     * @param BranchPickupHelper $branchPickupBranchPickupHelper
     */
    public function __construct(
        SessionManager $sessionManager,
        ListRestriction $listsFrontendRestrictedHelper,
        MessageManager $messageManager,
        BranchPickupHelperFactory $branchPickupHelperFactory,
        BranchpickupFactory $branchPickupBranchPickupFactory,
        CustomerSession $customerSession,
        BranchPickupHelperSession $branchPickupSessionHelper,
        SalesRepHelper $salesRepHelper,
        CustomerFactory $customerCustomerFactory,
        Data $commHelper,
        Generic $generic,
        ResponseInterface $response,
        UrlInterface $urlBuilder,
        ResponseFactory $responseFactory,
        BranchPickupHelper $branchPickupBranchPickupHelper,
        CartHelper $cartHelper
    ) {
        parent::__construct(
            $branchPickupHelperFactory,
            $branchPickupBranchPickupFactory,
            $customerSession,
            $branchPickupSessionHelper,
            $salesRepHelper,
            $customerCustomerFactory,
            $commHelper,
            $generic,
            $response,
            $urlBuilder,
            $responseFactory,
            $branchPickupBranchPickupHelper
        );
        $this->sessionManager = $sessionManager;
        $this->locationHelper = $this->_helper->getLocationHelper();
        $this->listsFrontendRestrictedHelper = $listsFrontendRestrictedHelper;
        $this->messageManager = $messageManager;
        $this->cartHelper =$cartHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->isHelperSet()) {
            return false;
        }
        $this->branchForceSelect();
        $this->customer = $observer->getCustomer();
        if ($this->isCustomerNew()) {
            $this->redirectToSuccessUrl();
        } else {
            $this->branchForceLogin();
        }
    }

    private function redirectToSuccessUrl()
    {
        if ($urlKey = $this->customer->getNewCustomerSuccessUrl()) {
            $this->redirectUrl = $urlKey;
            $this->redirectToUrl();
        }
    }

    private function branchForceLogin()
    {
        if (!$this->_helper->redirectToBranchpickup()) {
            return;
        }
        if ($this->isSalesRepRedirect()) {
            $this->redirectToUrl();
            exit;
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

    private function isSalesRepRedirect(): bool
    {
        $this->redirectUrl = $this->_helper->salesRepRedirect();

        return (boolean) $this->redirectUrl;
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

    private function isCustomerNew()
    {
        if (is_object($this->customer) && $this->sessionManager instanceof SessionManager) {
            return (boolean) $this->customer->getCustomerIsNew() || $this->sessionManager->getCustomerIsNew();
        }

        return false;
    }

    private function branchForceSelect()
    {
        if ($this->isInventoryViewEnabled()) {
            $defaultLocationCode = $this->locationHelper->getDefaultLocationCode();
            $this->validateCart($defaultLocationCode);
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

    private function validateCart($locationCode)
    {
        $productLocations = $this->_branchModel->checkProductLocations($locationCode);
        $cartItems = $this->_branchModel->getCartItems();
        $result = array_diff($cartItems, $productLocations);
        $passAddress = $this->listsFrontendRestrictedHelper->getLocationAddress($locationCode,'shipping');
        $urlEncodeVals = http_build_query($passAddress, '', '&');
        $checkAddressResult = $this->listsFrontendRestrictedHelper->checkProductAddressNew($urlEncodeVals,'shipping');
        $result = array_merge($result, $checkAddressResult);
        if (!empty($result)) {
            $cart = $this->cartHelper->getCart();
            $items = $cart->getItems();
            $collectTotals = false;
            foreach ($items as $item) {
                if (in_array($item->getProduct()->getId(), $result)) {
                    $collectTotals = true;
                    $item->isDeleted(true);
                    $error = __('Product %1 was removed from cart, as it is not valid for selected location.', $item->getProduct()->getSku());
                    $this->messageManager->addErrorMessage($error);
                }
            }
            if ($collectTotals === true) {
                $cart->getQuote()->setTotalsCollectedFlag(false);
                $cart->save();
            }
        }
        return;
    }
}
