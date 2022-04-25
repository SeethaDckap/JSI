<?php

/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Claimstatus;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action {

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory
     */
    protected $commErpMappingClaimstatusFactory;

    public function __construct(
    \Epicor\Comm\Controller\Adminhtml\Context $context, \Epicor\Comm\Model\Erp\Mapping\ClaimstatusFactory $commErpMappingClaimstatusFactory
    ) {
        $this->commErpMappingClaimstatusFactory = $commErpMappingClaimstatusFactory;
        parent::__construct($context);
    }

    public function execute() {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->commErpMappingClaimstatusFactory->create();
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
        $this->messageManager->addErrorMessage(__('Unable to find the shipping status to delete.'));
        $this->_redirect('*/*/');
    }

}
