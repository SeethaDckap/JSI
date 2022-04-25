<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes;

class Edit extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes
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

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Epicor\Comm\Model\Erp\Mapping\AttributesFactory $commErpMappingAttributesFactory,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Framework\Registry $registry
    ) {
        $this->commErpMappingAttributesFactory = $commErpMappingAttributesFactory;
        $this->backendSession = $backendSession;
        $this->registry = $registry;
    }
    public function execute()
    {

        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commErpMappingAttributesFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $data = $this->backendSession->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->backendSession->addError(__('Attribute Type does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->registry->register('erpattributes_mapping_data', $model);

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
    }

    }
