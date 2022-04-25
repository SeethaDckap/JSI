<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Observer;

use Magento\Customer\Model\Session as CustomerSession;

class EmptyCart extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $locationHelper;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Common\Helper\AccessFactory $commonAccessHelper,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Backend\Helper\Js $backendJsHelper,
        \Epicor\Common\Model\Access\Group\CustomerFactory $commonAccessGroupCustomerFactory,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\DataFactory $commonHelper,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Customer\Erpaccount\AddressFactory $commCustomerErpaccountAddressFactory,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Lists\Helper\Frontend\ProductFactory $listsFrontendProductHelper,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Framework\HTTP\Header $header,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Helper\Locations $locationHelper
    )
    {

        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->locationHelper = $locationHelper;
        parent::__construct(
            $request,
            $commonAccessHelper,
            $customerSession,
            $scopeConfig,
            $frameworkHelperDataHelper,
            $eventManager,
            $generic,
            $backendJsHelper,
            $commonAccessGroupCustomerFactory,
            $registry,
            $commonHelper,
            $backendAuthSession,
            $commCustomerErpaccountAddressFactory,
            $catalogProductFactory,
            $listsFrontendProductHelper,
            $catalogCategoryFactory,
            $header,
            $url,
            $response
        );
    }

    /**
     * ResetCartMsqRegistry from customer session to correct BSV when customer login from guest
     * while having items in cart.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $cart \Epicor\Comm\Model\Cart */

        $customerSession = $this->customerSession->create();
        $customer = $customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */
        $customerQuote = false;
        try {
            $customerSession->setCartMsqRegistry(array());
            $this->registry->unregister('QuantityValidatorObserver');
            $this->registry->register('QuantityValidatorObserver', 1);
            $customerQuote = $this->quoteRepository->getForCustomer($customerSession->getCustomerId());
            $this->registry->unregister('QuantityValidatorObserver');
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }

        $emptyCustomerQuote     = (!$customerQuote || ($customerQuote && $customerQuote->getItemsCount() == 0));
        $checkoutSessionQuoteId = $this->checkoutSession->getQuoteId();
        if (($emptyCustomerQuote  && !$checkoutSessionQuoteId)
            || ($this->isInventoryViewEnabled() && $emptyCustomerQuote)) {
            $this->registry->unregister('dont_send_bsv');
            $this->registry->register('dont_send_bsv', true);
        }
    }

    private function isInventoryViewEnabled()
    {
        if ($this->customerSession->create() instanceof CustomerSession) {
            return $this->customerSession->create()->isLoggedIn()
                && $this->locationHelper->isLocationsEnabled()
                && ($this->locationHelper->getLocationStyle() == 'inventory_view');
        }
        return false;
    }

}