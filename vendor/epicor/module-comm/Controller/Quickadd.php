<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller;


/**
 * Quick add to basket / wishlist controller
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
abstract class Quickadd extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Epicor\Comm\Model\Message\Request\MsqFactory
     */
    protected $commMessageRequestMsqFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
   // protected $generic;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistWishlistFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Model\Message\Request\MsqFactory $commMessageRequestMsqFactory,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
      //  \Magento\Framework\Session\Generic $generic,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\WishlistFactory $wishlistWishlistFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commMessageRequestMsqFactory = $commMessageRequestMsqFactory;
        $this->eventManager = $context->getEventManager();
        $this->wishlistHelper = $wishlistHelper;
       // $this->generic = $generic;
        $this->commHelper = $commHelper;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->wishlistWishlistFactory = $wishlistWishlistFactory;
        parent::__construct(
            $context
        );
    }

/**
     * Checks the prodcut using an MSQ
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @param integer $qty
     */
    protected function _checkProduct(&$product, $qty, $locationcode)
    { 
        $msq = $this->commMessageRequestMsqFactory->create();
        if ($msq->isActive()) {
            $msq->addLocations($locationcode);
            $msq->addProduct($product, $qty);
            $msq->sendMessage();
            //$this->registry->unregister('msq-processing');
            //$this->registry->register('msq-processing', true);
        }
    }

    /**
     * Adds a product to the wishlist
     * 
     * @param \Magento\Catalog\Model\Product $product
     * @param integer $qty
     */
    public function _addToWishlist($product, $qty)
    {

        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            $this->messageManager->addError(__('Could not update wishlist'));
            throw new \Magento\Framework\Exception\LocalizedException(__('Could not update wishlist'));
        }
        $requestParams = array(
            'product' => $product->getId(),
            'qty' => $qty
        );
        if($product->getTypeId() == 'grouped'){
            $data = $this->getRequest()->getPostValue();  
            unset($requestParams['qty']);
            $requestParams['super_group'] = array($data['super_group'] => $qty);
        }
        $buyRequest = $this->dataObjectFactory->create(['data'=>$requestParams]);
        $result = $wishlist->addNewItem($product, $buyRequest);
        if (is_string($result)) {
            throw new \Magento\Framework\Exception\LocalizedException($result);
        }
        $wishlist->save();
        $this->eventManager->dispatch(
            'wishlist_add_product', array(
            'wishlist' => $wishlist,
            'product' => $product,
            'item' => $result
            )
        );

        /**
         *  Set referer to avoid referring to the compare popup window
         */
        $this->wishlistHelper->calculate();

        $this->messageManager->addSuccess($product->getName() . ' has been added to your wishlist');
    }

    /**
     * Initialize product instance from request data
     *
     * @param string $sku - SKU to load
     * 
     * @return \Magento\Catalog\Model\Product || false
     */
    protected function _initProduct($sku, $productId = '')
    {
        $product = false;
        if ($sku || $productId) {
            $helper = $this->commHelper;
            /* @var $helper Epicor_Comm_Helper_Data */

            $product = $this->catalogProductFactory->create();
            /* @var $product Epicor_Comm_Helper_Product */

            if ($productId) {
                $product->load($productId);
            }

            if ($sku && $product->isObjectNew()) {
                $product = $helper->findProductBySku($sku, '', false);
            }
        }

        return $product;
    }

    /**
     * Retrieve wishlist object
     * 
     * (Taken from the Wishlist index controller)
     * 
     * @param int $wishlistId
     * @return \Magento\Wishlist\Model\Wishlist|bool
     */
    protected function _getWishlist($wishlistId = null)
    {
        try {
            $wishlist = $this->registry->registry('wishlist');
            if ($wishlist) {
                return $wishlist;
            }

        
            if (!$wishlistId) {
                $wishlistId = $this->getRequest()->getParam('wishlist_id');
            }
            $customerId = $this->customerSession->getCustomerId();
            /* @var Mage_Wishlist_Model_Wishlist $wishlist */
            $wishlist = $this->wishlistWishlistFactory->create();
            if ($wishlistId) {
                $wishlist->load($wishlistId);
            } else {
                $wishlist->loadByCustomerId($customerId, true);
            }

            if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
                return false;
            }

            $this->registry->register('wishlist', $wishlist);
        } catch (\Exception $e) {
            //M1 > M2 Translation Begin (Rule p2-5.1)
            //Mage::getSingleton('wishlist/session')->addException($e, __('Wishlist could not be created.')
            $this->messageManager->addExceptionMessage($e, __('Wishlist could not be created.'));
            //M1 > M2 Translation End
            return false;
        }

        return $wishlist;
    }
}
