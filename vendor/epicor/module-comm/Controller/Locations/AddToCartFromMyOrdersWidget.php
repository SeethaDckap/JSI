<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Locations;

class AddToCartFromMyOrdersWidget extends \Epicor\Comm\Controller\Locations
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
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $salesOrderItemFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Product $commProductHelper,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Sales\Model\Order\ItemFactory $salesOrderItemFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\Generic $generic)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->commonHelper = $commonHelper;
        $this->salesOrderItemFactory = $salesOrderItemFactory;
        $this->checkoutSession = $checkoutSession;
        parent::__construct(
            $context,
            $commProductHelper,
            $catalogProductFactory,
            $generic);
    }
public function execute()
    {
        if($this->checkoutSession->getQuote()->hasEccQuoteId()){
             $this->messageManager->addError('You can\'t add products while you have a quote in the basket');
             $this->_redirect('checkout/cart');
             return;
        }
        $helper = $this->commonHelper;
        /* @var $helper Epicor_Common_Helper_Data */

        $orderItems = $this->getRequest()->getParam('order_items');
        $products = array();
        if($orderItems){
            foreach ($orderItems as $orderItem) {
                $salesOrderItem = $this->salesOrderItemFactory->create()->load($orderItem);
                /* @var $salesOrderItem Mage_Sales_Model_Order_Item */
                $product = $salesOrderItem->getProduct();
                /* @var $product Epicor_Comm_Model_Product */
                $sku = $helper->stripProductCodeUOM($product->getSku());
                $products[] = array(
                    'sku' => $sku,
                    'qty' => 1
                );
            }
        }           
        $this->_massiveAddFromSku($products, 'last_ordered_items');
        $resultPage = $this->resultPageFactory->create();
                return $resultPage;
    }

    }
