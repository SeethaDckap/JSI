<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erpattributes;

class Delete extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erpattributes
{

/*
     * called by admin/epicorcomm_mapping_erpattributes/edit 
     * Blocks defined in adminhtml_epicorcomm_mapping_erpattributes_edit 
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
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->commErpMappingAttributesFactory = $commErpMappingAttributesFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->commErpMappingAttributesFactory->create();
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('The example has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $ $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
         $this->messageManager->addErrorMessage(__('Unable to find the example to delete.'));
        $this->_redirect('*/*/');
    }

    }
