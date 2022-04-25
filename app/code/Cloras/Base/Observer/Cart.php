<?php

namespace Cloras\Base\Observer;

use Magento\Framework\Event\ObserverInterface;

class Cart implements ObserverInterface
{
    private $registry = null;

    private $logger;

    private $helper;

    private $customerFactory;

    private $customerResourceFactory;

    private $customer;

    private $customerData;

    private $objectManager;

    private $session;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Cloras\Base\Helper\Data $helper,
        \Magento\Customer\Model\SessionFactory $session,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Data\Customer $customerModelData,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->registry                = $registry;
        $this->logger                  = $logger;
        $this->helper                  = $helper;
        $this->customerFactory         = $customerFactory;
        $this->customerResourceFactory = $customerResourceFactory;
        $this->customer                = $customer;
        $this->customerModelData            = $customerModelData;
        $this->productRepository       = $productRepository;
        $this->session                 = $session;
    }//end __construct()

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quoteitem = $observer->getEvent()->getData('quote_item');

        $product  = $observer->getEvent()->getData('product');
        
        $qty          = $quoteitem->getQty();
        $productPrice     = $product->getPrice();

        $itemId = '';
        $productIds = [];
        $sessionPrices = [];

        if ($product->getTypeId() == 'simple') {
            $itemId = $product->getSku();
        } else {
            $itemId = $quoteitem->getSku();
        }
              
        $productIds[] = $product->getId();
       
        if (!empty($productIds)) {

            $products = $this->helper->fetchAPIData(
                $productIds,
                'fetch_price',
                $sessionPrices = [],
                $qty = 0,
                $isCurrency = 0
            );

            $this->logger->info('product price', (array)$products);
            if (!empty($products)) {
                $productPrice = $this->getProductPrice($products, $product->getId(), $productPrice);
            }
        }
        
        $this->logger->info('cart observer price', (array)$productPrice);
        
        if (round($productPrice) != 0) {
            $quoteitem->setCustomPrice($productPrice);
            $quoteitem->setOriginalCustomPrice($productPrice);

            $quoteitem->getProduct()->setIsSuperMode(true);

            return $this;
        }
    }

    private function getProductPrice($products, $productId, $productPrice)
    {
        foreach ($products as $key => $productsData) {
            if (array_key_exists('productId', $productsData)) {
                if ($productId == $productsData['productId']) {
                    if (array_key_exists('price', $productsData)) {
                        $productPrice = $productsData['price'];
                    }
                }
            }
        }
        return $productPrice;
    }
}//end class
