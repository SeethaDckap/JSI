<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Warranty;

class Edit extends \Epicor\Dealerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Warranty
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\CardtypeFactory
     */
    protected $WarrantyFactory;


    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Dealerconnect\Model\Erp\Mapping\WarrantyFactory $WarrantyFactory)
    {
        $this->warrantyFactory = $WarrantyFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {

        $id = $this->getRequest()->getParam('id', null);
        $model = $this->warrantyFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $data = $this->_session->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Mapping does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->_registry->register('warraty_mapping_data', $model);

        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Warranty Mapping "%s"', $model->getCode())) : __('New Mapping'));


        return $resultPage;
    }

    }
