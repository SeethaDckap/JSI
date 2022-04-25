<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Cart;

class CouponPost extends \Epicor\Comm\Controller\Cart
{

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Coupon factory
     *
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

     public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Epicor\Comm\Helper\Locations $commLocationsHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistResourceModelItemCollectionFactory,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository)
    {
        $this->eventManager = $context->getEventManager();
        $this->couponFactory = $couponFactory;
        $this->quoteRepository = $quoteRepository;

        parent::__construct($context,
                $scopeConfig,
                $checkoutSession,
                $storeManager,
                $formKeyValidator,
                $checkoutCart,
                $commProductHelper,
                $commLocationsHelper,
                $catalogProductFactory,
                $customerSession,
                $wishlistResourceModelItemCollectionFactory);
    }

     /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $this->eventManager->dispatch('checkout_coupon_post', array());
        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code'));

        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return $this->_goBack();
        }

        try {
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
            }

            if ($codeLength) {
                $escaper = $this->_objectManager->get('Magento\Framework\Escaper');
                if (!$itemsCount) {
                    if ($isCodeLengthValid) {
                        $coupon = $this->couponFactory->create();
                        $coupon->load($couponCode, 'code');
                        if ($coupon->getId()) {
                            $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                            $this->messageManager->addSuccess(
                                __(
                                    'You used coupon code "%1".',
                                    $escaper->escapeHtml($couponCode)
                                )
                            );
                        } else {
                            $this->messageManager->addError(
                                __(
                                    'The coupon code "%1" is not valid.',
                                    $escaper->escapeHtml($couponCode)
                                )
                            );
                        }
                    } else {
                        $this->messageManager->addError(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                } else {
                    if ($isCodeLengthValid && $couponCode == $cartQuote->getCouponCode()) {
                        $this->messageManager->addSuccess(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addError(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                        $this->cart->save();
                    }
                }
            } else {
                $this->messageManager->addSuccess(__('You canceled the coupon code.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot apply the coupon code.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        $this->_checkoutSession->unsetData('bsv_sent_for_cart_page');
        return $this->_goBack();
    }

    /**
     * Set back redirect url to response
     *
     * @param type $backUrl
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _goBack($backUrl = null) {
        $resultRedirect = $this->resultRedirectFactory->create();
        $returnUrl = $this->getRequest()->getParam('return_url');

        if ($returnUrl) {

            if (!$this->_isUrlInternal($returnUrl)) {
                throw new \Magento\Framework\Exception\LocalizedException('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $resultRedirect->setUrl($returnUrl);
        } elseif (!$this->_scopeConfig->getValue('checkout/cart/redirect_to_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && !$this->getRequest()->getParam('in_cart') && !$this->getRequest()->getParam('update_config_value') && $backUrl = $this->_redirect->getRefererUrl()
        ) {
            $resultRedirect->setUrl($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $resultRedirect->setUrl($this->_url->getUrl('checkout/cart'));
        }

        return $resultRedirect;
    }
}
