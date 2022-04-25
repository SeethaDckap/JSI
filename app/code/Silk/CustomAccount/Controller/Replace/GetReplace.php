<?php

namespace Silk\CustomAccount\Controller\Replace;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Silk\CustomAccount\Model\ReplaceFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class GetReplace extends \Magento\Framework\App\Action\Action
{

    protected $replaceFactory;

    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        ReplaceFactory $replaceFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->replaceFactory = $replaceFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            $replace = $this->replaceFactory->create()->load($id);
            $response = [
                'status' => 'success',
                'state' => $replace->getData('data')
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
