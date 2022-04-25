<?php

namespace Cloras\Base\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;

class Updatecart implements ObserverInterface
{

    private $logger;

    private $helper;

    private $checkoutSession;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Cloras\Base\Helper\Data $helper,
        Session $checkoutSession
    ) {
        $this->logger                  = $logger;
        $this->helper                  = $helper;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getEvent()->getData('info');

        $cart = $observer->getEvent()->getData('cart');
               
        $convert_data = (array) $data;

        $cartKeyData = array_keys($convert_data);

        $this->setItemCustomPrice(
            $convert_data,
            $cartKeyData
        );
    }

    private function setItemCustomPrice(
        $convert_data,
        $cartKeyData
    ) {
        $sessionPrices = [];
        foreach ($convert_data[$cartKeyData[0]] as $itemId => $itemInfo) {
            $item = $this->checkoutSession->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || isset($itemInfo['qty']) && $itemInfo['qty'] == '0') {
                $this->removeItem($itemId);
                continue;
            }

            $items        = [];
            $proceed      = false;
            $productIds[] = $item->getProductId();
            $qty          = $itemInfo['qty'];
            $productPrice = 0;

            $products = $this->helper->fetchAPIData(
                $productIds,
                'fetch_price',
                $sessionPrices = [],
                $qty = 0,
                $isCurrency = 0
            );

            foreach ($products as $key => $productsData) {
                if (array_key_exists('productId', $productsData)) {
                    if ($item->getProductId() == $productsData['productId']) {
                        if (array_key_exists('price', $productsData)) {
                            $productPrice = $productsData['price'];
                        }
                    }
                }
            }

            if (round($productPrice) != 0) {
                $item->setOriginalCustomPrice($productPrice);
                $item->setCustomPrice($productPrice);
            }
        }
    }
}
