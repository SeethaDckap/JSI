<?php

namespace Silk\CustomAccount\Controller\Order;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Silk\CustomAccount\Model\QuoteFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class GetOrder extends \Magento\Framework\App\Action\Action
{

    protected $varFactory;

    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        \Magento\Variable\Model\VariableFactory $varFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->varFactory = $varFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $ordersJS = $this->varFactory->create()->loadByCode('orders');
            if($ordersJS){
                $orders = json_decode($ordersJS->getPlainValue(), true);
            }
            $response = [
                'status' => 'success',
                'state' => $orders[(int)$id - 1]
            ];

        } catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
        

        return $this->resultJsonFactory->create()->setData($response);
    }
}
