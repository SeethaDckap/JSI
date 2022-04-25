<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erporderstatus;

class Delete extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erporderstatus
{

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\ErporderstatusFactory
     */
    protected $customerconnectErpMappingErporderstatusFactory;


    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Customerconnect\Model\Erp\Mapping\ErporderstatusFactory $customerconnectErpMappingErporderstatusFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->customerconnectErpMappingErporderstatusFactory = $customerconnectErpMappingErporderstatusFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->customerconnectErpMappingErporderstatusFactory->create();
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('The mapping has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find the mapping to delete.'));
        $this->_redirect('*/*/');
    }

    }
