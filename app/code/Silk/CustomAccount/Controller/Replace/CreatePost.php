<?php

namespace Silk\CustomAccount\Controller\Replace;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Silk\CustomAccount\Model\ReplaceFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class CreatePost extends \Magento\Framework\App\Action\Action
{
    protected $customerSession;

    protected $replaceFactory;

    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        ReplaceFactory $replaceFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->customerSession = $customerSession;
        $this->replaceFactory = $replaceFactory;
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
                    $replace = $this->replaceFactory->create()->load($data['id']);
                    $customerId = $replace->getData('customer_id');
                }
                else{
                    $replace = $this->replaceFactory->create();
                    $customerId = $this->customerSession->getCustomerId();
                }


                $excludeList = ['step', 'quoteId', 'token', 'maxStep', 'id', "addresses", "images"];
                foreach ($excludeList as $exclude) {
                    unset($data[$exclude]);
                }

                $replace->setData('customer_id', $customerId);
                $replace->setData('data', json_encode($data));
                $replace->save();

                $response = [
                    'status' => 'success',
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
