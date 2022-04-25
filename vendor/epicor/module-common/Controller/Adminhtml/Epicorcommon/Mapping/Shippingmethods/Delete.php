<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Shippingmethods;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{



    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory
     */
    protected $commErpMappingShippingmethodFactory;


public function __construct( \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory $commErpMappingShippingmethodFactory)
{
    $this->commErpMappingShippingmethodFactory = $commErpMappingShippingmethodFactory;
    parent::__construct($context);
}

    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->commErpMappingShippingmethodFactory->create();
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('Mapping deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find the shipping method to delete.'));
        $this->_redirect('*/*/');
    }

    }
