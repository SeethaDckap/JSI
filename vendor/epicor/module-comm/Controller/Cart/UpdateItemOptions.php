<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Cart;

use Magento\Checkout\Model\Cart as CustomerCart;

class UpdateItemOptions extends \Epicor\Comm\Controller\Cart
{

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $catalogProduct;

    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutCartHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var type Magento\Framework\Locale\ResolverInterface
     */
    protected $resolver;

    /**
     * Magento\Framework\Escaper
     */
    protected $escaper;

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
        CustomerCart $cart,
        \Magento\Catalog\Model\Product $catalogProduct,
        \Magento\Checkout\Helper\Cart $checkoutCartHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Locale\ResolverInterface $resolver,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->catalogProduct = $catalogProduct;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->commProductHelper = $commProductHelper;
        $this->eventManager = $context->getEventManager();
        $this->checkoutCartHelper = $checkoutCartHelper;
        $this->logger = $logger;
        $this->cart = $cart;
        $this->resolver = $resolver;
        $this->escaper = $escaper;
        parent::__construct(
            $context,
            $scopeConfig, 
            $checkoutSession,
            $storeManager,
            $formKeyValidator, 
            $checkoutCart, 
            $commProductHelper,
            $commLocationsHelper, 
            $catalogProductFactory, 
            $customerSession,
            $wishlistResourceModelItemCollectionFactory 
            );
    }
    /**
     * Update product configuration for a cart item
     */
    public function execute()
    {
       // $cart = $this->checkoutCart->getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            $quoteItem = $this->cart->getQuote()->getItemById($id);
            if (!isset($params['qty'])) {
                $params['qty'] = 1;
            }
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->resolver->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
                $locationCode = $quoteItem->getEccLocationCode();
                $product = $this->catalogProduct->load($params['product']);

                $locHelper = $this->commLocationsHelper;
                /* @var $locHelper Epicor_Comm_Helper_Locations */

                $proHelper = $this->commProductHelper;
                /* @var $proHelper Epicor_Comm_Helper_Product */

                $locEnabled = $locHelper->isLocationsEnabled();
                if ($locEnabled) {
                    $newQty = $proHelper->getCorrectOrderQty($product, $params['qty'], $locEnabled, $locationCode, true);
                    if ($newQty['qty'] != $params['qty']) {
                        $params['qty'] = $newQty['qty'];
                        $message = $newQty['message'];
                    }
                }
            }

            if (!$quoteItem) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Quote item is not found.'));
            }

            $item = $this->cart->updateItem($id, new \Magento\Framework\DataObject($params));
            if (is_string($item)) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item));
            }
            if ($item->getHasError()) {
                throw new \Magento\Framework\Exception\LocalizedException(__($item->getMessage()));
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            $this->_checkoutSession->setCartWasUpdated(true);

            $this->eventManager->dispatch('checkout_cart_update_item_complete', array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __('%1 was updated in your shopping cart.', $this->escaper->escapeHtml($item->getProduct()->getName()));
                    $this->messageManager->addSuccessMessage($message);
                }
                $this->_goBack();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage($message);
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);
            if ($url) {
                return $this->resultRedirectFactory->create()->setUrl($url);
            } else {
                $cartUrl = $this->checkoutCartHelper->getCartUrl();
                return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl($cartUrl));
            }
        } catch (\Exception $e) {
                // $this->messageManager->addExceptionMessage($e, __('Cannot update the item.'));
               // $this->logger->critical($e);
                $this->_goBack();
        }
        $this->_redirect('*/*');
    }

}
