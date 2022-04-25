<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes;

class Delete extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes
{

/*
     * called by admin/epicorcomm_mapping_erpattributes/delete
     */

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\AttributesFactory
     */
    protected $commErpMappingAttributesFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Epicor\Comm\Model\Erp\Mapping\AttributesFactory $commErpMappingAttributesFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->commErpMappingAttributesFactory = $commErpMappingAttributesFactory;
        $this->backendSession = $backendSession;
    }
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->commErpMappingAttributesFactory->create();
                $model->setId($id);
                $model->delete();
                $this->backendSession->addSuccess(__('The example has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->backendSession->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->backendSession->addError(__('Unable to find the example to delete.'));
        $this->_redirect('*/*/');
    }

    }
