<?php

namespace Silk\CustomAccount\Controller\Quote;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Silk\CustomAccount\Model\QuoteFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class GetQuote extends \Magento\Framework\App\Action\Action
{

    protected $quoteFactory;

    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        QuoteFactory $quoteFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $quote = $this->quoteFactory->create()->load($id);
            $data = json_decode($quote->getData('data'), true);

            if(isset($data['products']) && !empty($data['products'])){
                foreach ($data['products'] as &$productData) {
                    $productData['item_id'] = '';
                }
            }

            $data['shipment'] = [
                "amount" => 0,
                "carrier_title" => '',
                "carrier_code" => '',
                "method_title" => '',
                "method_code" => ''
            ];

            $data['loading'] = false;
            $data['avaialableShipDate'] = ['to' => ''];
            $data['shipDate'] = '';

            $response = [
                'status' => 'success',
                'state' => json_encode($data)
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
