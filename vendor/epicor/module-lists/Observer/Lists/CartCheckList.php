<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Observer\Lists;

use Magento\Framework\Registry;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

class CartCheckList extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Epicor\Lists\Helper\Frontend\Product
     */
    protected $listsFrontendProductHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Epicor\Lists\Helper\Frontend
     */
    protected $listFrontendHelper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    private $registry;
    /**
     * 
     * @param \Epicor\Lists\Helper\Session $listsSessionHelper
     * @param \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper
     * @param \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Epicor\Lists\Helper\Frontend $listFrontendHelper
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Checkout\Helper\Cart $checkoutCartHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(\Epicor\Lists\Helper\Session $listsSessionHelper, \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper, \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogResourceModelProductCollectionFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager, \Epicor\Lists\Helper\Frontend $listFrontendHelper, CookieMetadataFactory $cookieMetadataFactory, \Magento\Checkout\Helper\Cart $checkoutCartHelper, \Magento\Quote\Api\CartRepositoryInterface $quoteRepository, Registry $registry
    ) {
        parent::__construct($listsSessionHelper, $listsFrontendProductHelper, $listsFrontendContractHelper, $storeManager, $catalogResourceModelProductCollectionFactory, $scopeConfig, $cookieManager, $listFrontendHelper, $cookieMetadataFactory);
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->listFrontendHelper = $listFrontendHelper;
        $this->_cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->quoteRepository = $quoteRepository;
        $this->registry                = $registry;
    }

    /**
     * 
     * Filter cart by List
     * 
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $cart = $this->checkoutCartHelper->getCart();
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()->setPath('/');

        if ($cart->getItemsCount() > 0 && $this->listFrontendHelper->listsEnabled() && $this->listsFrontendProductHelper->hasFilterableLists()) {
            $productIds = explode(',', $this->listsFrontendProductHelper->getActiveListsProductIds());
            $cartChanged = false;
            foreach ($cart->getItems() as $item) {
                $productId = $item->getProduct()->getId();

                $isValid = in_array($productId, $productIds);
                if ($isValid === false) {
                    $cart->removeItem($item->getItemId())->save();
                    $cartChanged = true;
                }
            }

            if ($cartChanged) {
                $this->registry->unregister('QuantityValidatorObserver');
                $this->registry->register('QuantityValidatorObserver', 1);
                $quote = $this->quoteRepository->get($cart->getQuote()->getId());
                $this->quoteRepository->save($quote);
                $this->registry->unregister('QuantityValidatorObserver');
            }
        }

        if ($this->listFrontendHelper->listsEnabled()) {
            $this->_cookieManager->setPublicCookie('isListFilterReq', 1, $metadata);
        }
        
        return $this;
    }
}
