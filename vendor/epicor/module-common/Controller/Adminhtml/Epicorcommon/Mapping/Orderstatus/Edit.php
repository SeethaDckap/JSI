<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Orderstatus;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Orderstatus
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\OrderstatusFactory
     */
    protected $commErpMappingOrderstatusFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Erp\Mapping\OrderstatusFactory $commErpMappingOrderstatusFactory)
    {
        $this->commErpMappingOrderstatusFactory = $commErpMappingOrderstatusFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commErpMappingOrderstatusFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getCode()) {
                $data = $this->_session->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                    //                 $model->setState($data['status']); don't know if this is required, ask
                }
            } else {
                $this->messageManager->addErrorMessage(__('Mapping does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->_registry->register('orderstatus_mapping_data', $model);

        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Mapping "%s"', $model->getCode())) : __('New Mapping'));


        return $resultPage;
    }

    }
