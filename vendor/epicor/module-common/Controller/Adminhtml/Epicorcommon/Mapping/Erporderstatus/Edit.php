<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erporderstatus;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erporderstatus
{

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\ErporderstatusFactory
     */
    protected $customerconnectErpMappingErporderstatusFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Customerconnect\Model\Erp\Mapping\ErporderstatusFactory $customerconnectErpMappingErporderstatusFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
        )
    {
        $this->customerconnectErpMappingErporderstatusFactory = $customerconnectErpMappingErporderstatusFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->customerconnectErpMappingErporderstatusFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getCode()) {
                $data = $this->_session->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Mapping does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->_registry->register('erporderstatus_mapping_data', $model);
        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Mapping "%s"', $model->getCode())) : __('New Mapping'));


        return $resultPage;
    }

    }
