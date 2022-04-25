<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Locations;

class AddToCartFromWishlist extends \Epicor\Comm\Controller\Locations
{

    /**
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Epicor\Common\Helper\Data
     */
    protected $commonHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory
     */
    protected $wishlistResourceModelItemCollectionFactory;



    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Session\Generic $generic,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory $wishlistResourceModelItemCollectionFactory
    )
    {
        $this->commonHelper = $commonHelper;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->wishlistResourceModelItemCollectionFactory = $wishlistResourceModelItemCollectionFactory;

        parent::__construct(
            $context,
            $commProductHelper,
            $catalogProductFactory,
            $generic);
    }
public function execute()
    {
        $helper = $this->commonHelper;
        /* @var $helper Epicor_Common_Helper_Data */

        $customer = $this->customerSessionFactory->create();
        $wishlist = $this->wishlistResourceModelItemCollectionFactory->create()->addCustomerIdFilter($customer->getId())
            ->addStoreData();
        $qty = $this->getRequest()->getParam('qty');

        $products = array();
        foreach ($wishlist as $item) {
            $product = $this->catalogProductFactory->create()->load($item->getProductId());
            /* @var $product Epicor_Comm_Model_Product */
            $sku = $helper->stripProductCodeUOM($product->getSku());
            $products[] = array(
                'sku' => $sku,
                'qty' => isset($qty[$item->getId()]) ? trim($qty[$item->getId()]) : 1,
                'wishlist_item_id' => $item->getId()
            );
        }

        $this->_massiveAddFromSku($products, 'wishlist');
    }

    }
