<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Elements\Controller\Adminhtml\Elements;
/**
 * Elements Payment  
 * 
 * @category    Epicor
 * @package     Epicor_Elements
 * @author      Epicor Web Sales Team
 */



class Edit extends \Epicor\Elements\Controller\Adminhtml\Elements
{

    protected $resultPageFactory;

    protected $registry;
    
    protected $elementsFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Epicor\Elements\Model\ElementsFactory $elementsFactory 
   ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->elementsFactory  = $elementsFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Details Of Elements  Transaction action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        //Get ID and create model
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->elementsFactory->create();
        
        // checking Id is there or not
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Transaction no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $transactionId = $model->getTransactionId();
        }
        $this->_coreRegistry->register('current_transaction', $model);
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb('View Transaction','View Transaction');
        $title = $this->getTransactionTitle($transactionId);
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }

    /**
     * Get the page title for transaction
     * @return object|string
     */
    public function getTransactionTitle($transactionId=null)
    {
        if ($transactionId) {
            return __(sprintf("Transaction# %s", $transactionId));
        } else {
            return __('Transaction Details');
        }
    }    
}
