<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Miscellaneouscharges;

class Delete extends \Epicor\Customerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Miscellaneouscharges
{

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\MiscellaneouschargesFactory
     */
    protected $erpMappingMiscFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Customerconnect\Model\Erp\Mapping\MiscellaneouschargesFactory $erpMappingMiscFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->erpMappingMiscFactory = $erpMappingMiscFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->erpMappingMiscFactory->create();
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('The Miscellaneouscharges Charge Code has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find the Miscellaneouscharges Charge Code to delete.'));
        $this->_redirect('*/*/');
    }

    }
