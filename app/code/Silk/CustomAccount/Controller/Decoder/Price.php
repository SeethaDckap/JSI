<?php

namespace Silk\CustomAccount\Controller\Decoder;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Checkout\Model\Session as CheckoutSession;
use Silk\SyncDecoder\Model\Message\Request\BsvFactory;

class Price extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;

    protected $checkoutSession;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        BsvFactory $bsvFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->bsvFactory = $bsvFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            $sku = $this->getRequest()->getParam('sku');
            $quote = $this->checkoutSession->getQuote();
            $bsv = $this->bsvFactory->create()->setQuote($quote)->setSku($sku);
            $price = $bsv->sendMessage();
            $result = [
                'price' => number_format((float)$price, 2, '.', '')
            ];
            return $this->resultJsonFactory->create()->setData($result);

        } catch (\Exception $e) {
            return $this->resultJsonFactory->create()->setData([]);
        }
    }
}
