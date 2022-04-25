<?php

/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Claimstatus;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Claimstatus {

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory
     */
    protected $commErpMappingClaimstatusFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
    \Epicor\Comm\Controller\Adminhtml\Context $context, \Magento\Backend\Model\Auth\Session $backendAuthSession, \Magento\Framework\DataObjectFactory $dataObjectFactory, \Epicor\Comm\Model\Erp\Mapping\ClaimstatusFactory $commErpMappingClaimstatusFactory) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commErpMappingClaimstatusFactory = $commErpMappingClaimstatusFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute() {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commErpMappingClaimstatusFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getClaimStatus()) {
                $data = $this->_session->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Mapping does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->_registry->register('claimstatus_mapping_data', $model);
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
                ->prepend($model->getId() ? 'Edit Mapping ' . $model->getData('erp_code') : __('New Mapping'));


        return $resultPage;
    }

}
