<?php

/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Claimstatus;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Claimstatus {

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory
     */
    protected $commErpMappingClaimstatusFactory;

    public function __construct(
    \Epicor\Comm\Controller\Adminhtml\Context $context, \Magento\Backend\Model\Auth\Session $backendAuthSession, \Epicor\Comm\Helper\Data $commHelper, \Epicor\Comm\Model\Erp\Mapping\ClaimstatusFactory $commErpMappingClaimstatusFactory
    ) {
        $this->commHelper = $commHelper;

        $this->commErpMappingClaimstatusFactory = $commErpMappingClaimstatusFactory;

        parent::__construct($context, $backendAuthSession);
    }

    public function execute() {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $model = $this->commErpMappingClaimstatusFactory->create();
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $erpCode = $model->getClaimStatus();
            }


            $model->setData($data);

            $this->_session->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $model->save();

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving mapping'));
                }
                $message = '"' . $model->getData('erp_code') . '" Mapping was successfully saved.';
                $this->messageManager->addSuccessMessage($message);
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
