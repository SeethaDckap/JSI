<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model;


class Cart extends \Magento\Checkout\Model\Cart
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Helper\LocationsFactory
     */
    protected $commLocationsHelper;

    /**
     * @var \Epicor\Lists\Helper\FrontendFactory
     */
    protected $listsFrontendHelper;

    /**
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Framework\Message\Factory
     */
    protected $factory;

    /*
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\ResourceModel\Cart $resourceCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Comm\Helper\LocationsFactory $commLocationsHelper,
        \Epicor\Lists\Helper\FrontendFactory $listsFrontendHelper,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Framework\Message\Factory $factory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->storeManager = $storeManager;
        $this->commLocationsHelper = $commLocationsHelper;
        $this->listsFrontendHelper = $listsFrontendHelper;
        $this->eventManager = $eventManager;
        $this->commProductHelper = $commProductHelper;
        $this->factory = $factory;
        parent::__construct(
            $eventManager,
            $scopeConfig,
            $storeManager,
            $resourceCart,
            $checkoutSession,
            $customerSession,
            $messageManager,
            $stockRegistry,
            $stockState,
            $quoteRepository,
            $productRepository,
            $data
        );
    }

    public function save()
    {
        try {
            parent::save();
            return $this;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
        } catch (\Exception $e) {
            $this->messageManager->addException($e,
                __('We can\'t add this item to your shopping cart right now.')
            );
        }
    }
    /**
     * Convert order item to quote item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param mixed $qtyFlag if is null set product qty like in order
     * @return \Magento\Checkout\Model\Cart
     */
    public function addOrderItem($orderItem, $qtyFlag = null)
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        if ($orderItem->getParentItem() === null) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                /**
                 * We need to reload product in this place, because products
                 * with the same id may have different sets of order attributes.
                 */
                $product = $this->productRepository->getById($orderItem->getProductId(), false, $storeId, true);
            } catch (NoSuchEntityException $e) {
                return $this;
            }
            if ($product->getEccConfigurator()) {
                $this->messageManager->addNoticeMessage(__("Can't add %1 to Basket", $orderItem->getName()));
                return $this;
            }

            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new \Magento\Framework\DataObject($info);
            if ($qtyFlag === null) {
                $info->setQty($orderItem->getQtyOrdered());
            } else {
                $info->setQty(1);
            }

            $this->addProduct($product, $info);
        }
        return $this;
    }

    /**
     * Add product to shopping cart (quote)
     *
     * @param int|\Magento\Catalog\Model\Product $productInfo
     * @param mixed $requestInfo
     * @return  \Magento\Checkout\Model\Cart
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProduct($productInfo, $requestInfo = null)
    {
        $locHelper = $this->commLocationsHelper->create();
        /* @var $locHelper \Epicor\Comm\Helper\Locations */

        $listHelper = $this->listsFrontendHelper->create();
        /* @var $listHelper \Epicor\Lists\Helper\Frontend */

        $isNewLine = isset($requestInfo['force']);

        if ($locHelper->isLocationsEnabled() || $listHelper->listsEnabled() || $isNewLine) {
            try {
                $quote = $this->getQuote();
                /* @var $quote \Epicor\Comm\Model\Quote */
                $quote->addOrUpdateLine($productInfo, $requestInfo, $isNewLine);
            } catch (\Exception $e) {
                $redirectUrl = ($productInfo->hasOptionsValidationFail()) ? $productInfo->getUrlModel()->getUrl(
                        $productInfo, array('_query' => array('startcustomization' => 1))
                    ) : $productInfo->getProductUrl();
                $this->getCheckoutSession()->setRedirectUrl($redirectUrl);
                if ($this->getCheckoutSession()->getUseNotice() === null) {
                    $this->getCheckoutSession()->setUseNotice(true);
                }
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        } else {
            return parent::addProduct($productInfo, $requestInfo);
        }
        $product = $this->_getProduct($productInfo);
        $productId = $product->getId();
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $this;
    }


    /**
     * Update cart items information
     *
     * @param   array $data
     * @return  \Magento\Checkout\Model\Cart
     */
    public function updateItems($data)
    {
       
        $infoDataObject = new \Magento\Framework\DataObject($data);
        
        $this->_eventManager->dispatch(
            'checkout_cart_update_items_before',
            ['cart' => $this, 'info' => $infoDataObject]
        );
        

        $locHelper = $this->commLocationsHelper->create();
        /* @var $locHelper Epicor_Comm_Helper_Locations */

        $proHelper = $this->commProductHelper;
        /* @var $helper Epicor_Comm_Helper_Product */

        $locEnabled = $locHelper->isLocationsEnabled();

        /* @var $messageFactory Mage_Core_Model_Message */
        $messageFactory = $this->factory;
        $session = $this->getCheckoutSession();
        $qtyRecalculatedFlag = false;
        foreach ($data as $itemId => $itemInfo) {
            $item = $this->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || (isset($itemInfo['qty']) && $itemInfo['qty'] == '0')) {
                $this->removeItem($itemId);
                continue;
            }

            $qty = isset($itemInfo['qty']) ? $itemInfo['qty'] : false;

            $product = $item->getProduct();
            $productId = $product->getId();
            if ($locEnabled) {
                $locationCode = $item->getEccLocationCode();
                $newQty = $proHelper->getCorrectOrderQty($product, $qty, $locEnabled, $locationCode, true);

                //Minimum and Maximum Qty check for product
                if ($newQty['qty'] != $qty) {
                    $qty = $newQty['qty'];
                    $itemInfo['before_suggest_qty'] = $newQty['qty'];
                    $message = $newQty['message'];
                    $this->messageManager->addSuccessMessage($message);
                }
            }
            if ($qty > 0) {
                $item->setQty($qty);

                $itemInQuote = $this->getQuote()->getItemById($item->getId());

                if (!$itemInQuote && $item->getHasError()) {
                    throw new \Magento\Framework\Exception\LocalizedException($item->getMessage());
                }

                if (isset($itemInfo['before_suggest_qty']) && ($itemInfo['before_suggest_qty'] != $qty)) {
                    $qtyRecalculatedFlag = true;
                    $message = $messageFactory->notice(__('Quantity was recalculated from %d to %d', $itemInfo['before_suggest_qty'], $qty));
                    $session->addQuoteItemMessage($item->getId(), $message);
                }
            }
        }

        if ($qtyRecalculatedFlag) {
            $session->addNotice(
                __('Some products quantities were recalculated because of quantity increment mismatch')
            );
        }

          $this->_eventManager->dispatch(
            'checkout_cart_update_items_after',
            ['cart' => $this, 'info' => $infoDataObject]
        );
          
        return $this;
    }
}
