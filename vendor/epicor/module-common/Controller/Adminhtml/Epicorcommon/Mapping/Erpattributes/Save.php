<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erpattributes;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erpattributes
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
        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->commErpMappingAttributesFactory->create();
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
            $model->setData($data);

            $this->_session->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $model->save();

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error Saving Attribute Yype'));
                }

                $this->messageManager->addSuccess(__('Attribute Type Was Successfully Saved.'));
                $this->_session->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (\Exception $e) {
               $this->messageManager->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        $this->messageManager->addError(__('No data found to save'));
        $this->_redirect('*/*/');
    }

    }
