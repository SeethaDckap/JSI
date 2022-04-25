<?php

/**
 * Copyright Â© 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Epicor\Comm\CustomerData;

/**
 * Wishlist section
 */
class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist {

    /**
     * @var string
     */
    const SIDEBAR_ITEMS_NUMBER = 3;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var \Magento\Wishlist\Block\Customer\Sidebar
     */
    protected $block;
    protected $locHelper;

    /**
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Sidebar $block
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \Magento\Framework\App\ViewInterface $view
     */
    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory
     */
    protected $pricing;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */

    /**
     * @var \Epicor\Comm\Helper\LazyLoader
     */
    protected $loaderHelper;

    public function __construct(
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Sidebar $block,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\App\ViewInterface $view,
        \Epicor\Comm\Helper\Locations $locHelper,
        \Magento\Framework\Pricing\Helper\Data $pricing,
        \Epicor\Comm\Helper\data $epicorComm,
        \Epicor\Comm\Helper\LazyLoader $loaderHelper
    ) {
        $this->locHelper = $locHelper;
        $this->wishlistHelper = $wishlistHelper;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->block = $block;
        $this->view = $view;
        $this->pricing = $pricing;
        $this->epicorComm = $epicorComm;
        $this->loaderHelper = $loaderHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData() {
        $counter = $this->getCounter();
        return [
            'counter' => $counter,
            'items' => $counter ? $this->getItems() : [],
        ];
    }

    /**
     * @return string
     */
    protected function getCounter() {
        $registry = $this->locHelper->getRegistry();
        if (!$registry->registry('SkipEvent')) {
            $registry->register('SkipEvent', true);
        }
        $counter = $this->createCounter($this->wishlistHelper->getItemCount());
        $registry->unregister('SkipEvent');
        return $counter;
    }

    /**
     * Create button label based on wishlist item quantity
     *
     * @param int $count
     * @return \Magento\Framework\Phrase|null
     */
    protected function createCounter($count) {
        if ($count > 1) {
            return __('%1 items', $count);
        } elseif ($count == 1) {
            return __('1 item');
        }
        return null;
    }

    /**
     * Get wishlist items
     *
     * @return array
     */
    protected function getItems() {
        $this->view->loadLayout();

        $collection = $this->wishlistHelper->getWishlistItemCollection();
        $collection->clear()->setPageSize(self::SIDEBAR_ITEMS_NUMBER)
                ->setInStockFilter(true)->setOrder('added_at');

        $items = [];
        foreach ($collection as $wishlistItem) {
            $items[] = $this->getItemData($wishlistItem);
        }
        return $items;
    }

    /**
     * Retrieve wishlist item data
     *
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @return array
     */
    protected function getItemData(\Magento\Wishlist\Model\Item $wishlistItem) {

        $product = $wishlistItem->getProduct();
        $groupOrConfigurable = in_array($product->getTypeId(), array('grouped', 'configurable')) ? true : false;
        $locationsEnabled = $this->locHelper->isLocationsEnabled() ? true : false;        
        
        return [
            'image' => $this->getImageData($product),
            'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
            'product_name' => $product->getName(),
            'product_price' => $this->block->getProductPriceHtml(
                    $product, 'final_price', \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST, ['item' => $wishlistItem]
            ),
            'product_is_saleable_and_visible' => $product->isSaleable() && $product->isVisibleInSiteVisibility(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem, true),
            'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem, true),
            'locations_or_group_or_configurable' => $locationsEnabled || $groupOrConfigurable,
            'isLoading' => true,
            'isLazyLoad' => $this->loaderHelper->isLazyLoad() ?: false,
        ];
    }

    /**
     * Retrieve product image data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Block\Product\Image
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getImageData($product) {
        /** @var \Magento\Catalog\Helper\Image $helper */
        $helper = $this->imageHelperFactory->create()
                ->init($product, 'wishlist_sidebar_block');

        $template = $helper->getFrame() ? 'Magento_Catalog/product/image' : 'Magento_Catalog/product/image_with_borders';

        $imagesize = $helper->getResizedImageInfo();

        $width = $helper->getFrame() ? $helper->getWidth() : (!empty($imagesize[0]) ? $imagesize[0] : $helper->getWidth());

        $height = $helper->getFrame() ? $helper->getHeight() : (!empty($imagesize[1]) ? $imagesize[1] : $helper->getHeight());

        return [
            'template' => $template,
            'src' => $helper->getUrl(),
            'width' => $width,
            'height' => $height,
            'alt' => $helper->getLabel(),
        ];
    }

}

