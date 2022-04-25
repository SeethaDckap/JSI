<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Controller\Lists;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
class Cartcheck extends \Magento\Framework\App\Action\Action {

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

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Epicor\Lists\Helper\Frontend\Product $listsFrontendProductHelper, \Magento\Checkout\Helper\Cart $checkoutCartHelper, \Magento\Quote\Api\CartRepositoryInterface $quoteRepository, \Epicor\Lists\Helper\Frontend $listFrontendHelper, \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager, CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->listsFrontendProductHelper = $listsFrontendProductHelper;
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->quoteRepository = $quoteRepository;
        $this->listFrontendHelper = $listFrontendHelper;
        $this->_cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        parent::__construct(
                $context
        );
    }

    public function execute() {
        $productHelper = $this->listsFrontendProductHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend_Product */
        $listHelper = $this->listFrontendHelper;
        /* @var $helper Epicor_Lists_Helper_Frontend */
        $cart = $this->checkoutCartHelper->getCart();
        /* @var $cart Epicor_Comm_Model_Cart */
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()->setPath('/');
        if ($cart->getItemsCount() > 0) {
            if ($listHelper->listsEnabled() && $productHelper->hasFilterableLists()) {
                $productIds = explode(',', $productHelper->getActiveListsProductIds());
                foreach ($cart->getItems() as $item) {
                    $productId = $item->getProduct()->getId();

                    $isValid = in_array($productId, $productIds);
                    if ($isValid === false) {
                        $cart->removeItem($item->getItemId())->save();
                    }
                }
                $quote = $this->quoteRepository->get($cart->getQuote()->getId());
                $this->quoteRepository->save($quote);
                
            }
        }
        $this->_cookieManager->setPublicCookie('isListFilterReq', 1, $metadata);
        $result = array(
            'type' => 'success'
        );

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($result));
    }

}
