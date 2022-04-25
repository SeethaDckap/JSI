<?php

namespace Cloras\Base\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;

class Base extends Template
{

    private $wishlistProvider;
    
    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param RequestInterface $requestInterface
     * @param WishlistProviderInterface $wishlistProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RequestInterface $requestInterface,
        WishlistProviderInterface $wishlistProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->requestInterface = $requestInterface;
        $this->wishlistProvider = $wishlistProvider;
    }

    /**
     * Get Wishlist Product Ids
     *
     * @return array
     */
    public function getWishlistProductIds()
    {
        $currentUserWishlist = $this->wishlistProvider->getWishlist();
        if ($currentUserWishlist) {
            $wishlistItems = $currentUserWishlist->getItemCollection();
            $productIds = [];
            foreach ($wishlistItems as $wishlistItem) {
                $productIds[] = $wishlistItem->getProductId();
            }
            return $productIds;
        }
    }

    /**
     * Get Current action name
     *
     * @return string
     */
    public function getCurrentActionName()
    {
        return $this->requestInterface->getFullActionName();
    }

    /**
     * Get Current Category
     *
     * @return int
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }
}
