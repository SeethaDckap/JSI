<?php

namespace Silk\CustomAccount\Controller\Quote;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Silk\CustomAccount\Model\QuoteFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class CreatePost extends \Magento\Framework\App\Action\Action
{
    protected $customerSession;

    protected $quoteFactory;

    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        QuoteFactory $quoteFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            $data = $this->getRequest()->getPostValue();
            if(isset($data['data'])){
                $data = json_decode($data['data'], true);
                $customerId = $this->customerSession->getCustomerId();
                if($data['id']){
                    $quote = $this->quoteFactory->create()->load($data['id']);
                    $customerId = $quote->getData('customer_id');
                }
                else{
                    $quote = $this->quoteFactory->create();
                    $customerId = $this->customerSession->getCustomerId();
                }


                $excludeList = ['step', 'quoteId', 'token', 'maxStep', "addresses",'warehouseMapping','doorStyles','shippingMethods','pickupAddress'];
                foreach ($excludeList as $exclude) {
                    unset($data[$exclude]);
                }

                if(!$data['id']){
                    unset($data['id']);
                }

                $quote->setData('customer_id', $customerId);
                $quote->setData('data', json_encode($data));
                $quote->save();

                $response = [
                    'status' => 'success',
                    'id' => $quote->getId(),
                    'error' => ''
                ];
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
        

        return $this->resultJsonFactory->create()->setData($response);
    }
}
