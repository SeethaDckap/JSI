<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Warranty;

class Delete extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Warranty
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\CardtypeFactory
     */
    protected $WarrantyFactory;
    
    
    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
         \Epicor\Dealerconnect\Model\Erp\Mapping\WarrantyFactory $WarrantyFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->warrantyFactory = $WarrantyFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->warrantyFactory->create();
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('Warranty has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find the warranty to delete.'));
        $this->_redirect('*/*/');
    }

    }
