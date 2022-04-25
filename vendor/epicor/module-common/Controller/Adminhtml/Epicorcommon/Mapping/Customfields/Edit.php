<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Customfields;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Customfields
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\CurrencyFactory
     */
    protected $customfieldsFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Supplierconnect\Model\Erp\Mapping\CustomfieldsFactory $customfieldsFactory)
    {
        $this->customfieldsFactory = $customfieldsFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->customfieldsFactory->create();
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
        $this->_registry->register('customfields_mapping_data', $model);

        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Mapping "%s"', $model->getErpCode())) : __('New Mapping'));


        return $resultPage;
    }




    }
