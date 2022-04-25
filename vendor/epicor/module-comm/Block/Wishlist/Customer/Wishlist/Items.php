<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Wishlist\Customer\Wishlist;

/**
 * Wishlist block customer items
 *
 * @api
 * @since 100.0.2
 */
class Items extends \Magento\Wishlist\Block\Customer\Wishlist\Items
{
    /**
     * @var \Epicor\Comm\Helper\LazyLoader
     */
    protected $loaderHelper;

    /**
     * Items constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Epicor\Comm\Helper\LazyLoader $loaderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Comm\Helper\LazyLoader $loaderHelper,
        array $data = []
    ) {
        $this->loaderHelper = $loaderHelper;
        parent::__construct($context, $data);
    }

    /**
     *
     * @return array|\Magento\Wishlist\Block\Customer\Wishlist\Item\Column[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getColumns()
    {
        $columns = [];
        $restrictAjaxBlock = $this->loaderHelper->getWishListRestrictBlock();
        $EnableLazyLoad = $this->loaderHelper->isLazyLoad();
        foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $key => $child) {
            /**
             * Skip to load block if lazy load is Enable
             * and same skipped blocked load by ajax
             * Epicor\Comm\Controller\Wishlist\Index
             *
             */
            if ($EnableLazyLoad && in_array($key, $restrictAjaxBlock)) {
                continue;
            }

            if ($child instanceof \Magento\Wishlist\Block\Customer\Wishlist\Item\Column && $child->isEnabled()) {
                $columns[] = $child;
            }
        }
        return $columns;
    }
}
