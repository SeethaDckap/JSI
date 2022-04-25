<?php

namespace Silk\CustomAccount\Controller\Quote;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Silk\CustomAccount\Model\QuoteFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class DeletePost extends \Magento\Framework\App\Action\Action
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
            $id = $this->getRequest()->getParam('id');
            if($id){
                $quote = $this->quoteFactory->create()->load($id);
                if($this->customerSession->getCustomerId() == $quote->getCustomerId()){
                    $quote->delete();
                    $this->messageManager->addSuccess(__('Quote has been deleted.'));
                }
                else{
                    $this->messageManager->addError(__('This quote does not belong to current customer.'));
                }
                
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something is wrong with this quote. Please try again later.'));
        }

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
