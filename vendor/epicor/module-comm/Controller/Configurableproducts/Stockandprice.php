<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Configurableproducts;

use Epicor\Comm\Model\Product;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\Stock\ItemFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Stockandprice extends \Epicor\Comm\Controller\Configurableproducts
{

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;
    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    private $locationHelper;

    /**
     * @var \Epicor\Comm\Block\Catalog\Product\View\Locations
     */
    protected  $locations;

    /**
     *
     * @var \Epicor\Comm\Helper\Product
     */
    protected $commProductHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ItemFactory
     */
    private $catalogInventoryStockItemFactory;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Comm\Block\Catalog\Product\View\Locations $locations,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $catalogInventoryStockItemFactory,
        \Epicor\Comm\Helper\Locations $locationHelper = null
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->checkoutCart = $checkoutCart;
        $this->checkoutSession = $checkoutSession;
        $this->commHelper = $commHelper;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->eventManager = $context->getEventManager();
        $this->customerSession = $customerSession;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->locations = $locations;
        $this->commProductHelper = $commProductHelper;
        $this->scopeConfig = $this->commHelper->getScopeConfig();
        $this->catalogInventoryStockItemFactory = $catalogInventoryStockItemFactory;
        parent::__construct(
            $context
        );
        $this->locationHelper = $locationHelper;
    }



    public function execute()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        $selectedConfig = $this->getRequest()->getParam('selected_configurable_option');

        $childProduct = false;
        if ($productId) {
            $parentProduct = $this->catalogProductFactory->create()
                ->setStoreId($this->storeManager->getStore()->getId())
                ->load($productId);
            if ($parentProduct->getId()) {
                $_children = $parentProduct->getTypeInstance()->getUsedProducts($parentProduct);
                foreach ($_children as $child){
                    if($child->getID() == $selectedConfig) {
                        $childProduct = $child; //->setId($product->getParentId());
                        break;
                    }
                }
            }
        }

        if ($childProduct) {
            $collection = $this->commProductHelper->getProductCollectionByIds($childProduct->getId());
            $parentProductId = $childProduct->getParentId();
            $childProduct = $collection->getFirstItem();
            $childProduct->setParentId($parentProductId);
            $this->registry->register('product', $childProduct);
            $this->registry->register('current_product', $childProduct);
            $blockHtml = $this->_view->loadLayout('empty')->getLayout()->getBlock('product.info.stockandprice')->toHtml();
            $result = array('html' => $blockHtml, 'location_status' => $this->getLocationStatus());
            $result['allOutOfStock'] = $this->allOutOfStock($childProduct);
        } else {
            $result = array('error' => 'Product not found');
        }

        $this->getResponse()->setBody(json_encode($result));
    }

    private function getLocationStatus()
    {
        return $this->locationHelper->isLocationsEnabled() === true ? 1 : 0;
    }

    /**
     * Check if all options for a configurable product are out of stock(applies to display out of stock= No)
     *
     * @param Product $product
     * @return bool
     */
    public function allOutOfStock($product)
    {
        $canShowOutOfStock = $this->locationHelper->canShowOutOfStock($product);
        $locations = $this->locations->getLocations($product);
        $count = 0;
        if (count($locations) > 0) {
            foreach ($locations as $location) {
                $product->setToLocationPrices($location);
                $erpStock = $product->getErpStock();
                if ($this->isShowOutOfStock($product)) {
                    if (!$erpStock || $erpStock <= 0) {
                        $count++;
                    }
                }
            }
        }
        return (($count && (count($locations) == $count)) || !$canShowOutOfStock) ? true : false;

    }


    /**
     * Can show out of stock
     *
     * @params Product $product
     *
     * @return boolean
     */
    private function isShowOutOfStock($product)
    {
        $msqAlwaysInStock = $this->scopeConfig->isSetFlag('epicor_comm_enabled_messages/msq_request/products_always_in_stock', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        /* @var $stockItem Item */
        $stockItem = $this->catalogInventoryStockItemFactory->create();
        $stockItem->getResource()->loadByProductId($stockItem, $product->getId(), $stockItem->getStockId());
        $msqAlwaysInStock = ($msqAlwaysInStock || $stockItem->getBackorders());

        $hideOutOfStock = !$this->commHelper->isShowOutOfStock() && !$product->getIsEccNonStock();
        return ($hideOutOfStock || !$msqAlwaysInStock);

    }//end isShowOutOfStock()


}
