<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Rmastatus;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Rmastatus
{

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\RmastatusFactory
     */
    protected $customerconnectErpMappingRmastatusFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Customerconnect\Model\Erp\Mapping\RmastatusFactory $customerconnectErpMappingRmastatusFactory)
    {
        $this->customerconnectErpMappingRmastatusFactory = $customerconnectErpMappingRmastatusFactory;
        parent::__construct($context, $backendAuthSession);
    }


    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->customerconnectErpMappingRmastatusFactory->create();
            /* @var $model Epicor_Customerconnect_Model_Erp_Mapping_Rmastatus */
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }

            $model->setData($data);
            if (isset($data['is_rma_deleted'])) {
                $model->setIsRmaDeleted(true);
            } else {
                $model->setIsRmaDeleted(false);
            }


            $this->_session->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $model->save();

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving mapping'));
                }

                $this->messageManager->addSuccessMessage(__($model->getCode() . ' Mapping was successfully saved.'));
                $this->_session->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        $this->messageManager->addErrorMessage(__('No data found to save'));
        $this->_redirect('*/*/');
    }

    }
