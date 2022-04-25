<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\DataMapping;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\DataMapping
{
    /**
     * @var \Epicor\Common\Model\DataMapping
     */
    protected $dataMappingFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Common\Model\DataMappingFactory $dataMappingFactory)
    {
        $this->dataMappingFactory = $dataMappingFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->dataMappingFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $data = $this->_session->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Data Mapping does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->_registry->register('ecc_datamapping_data', $model);

        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Data Mapping "%s"', $model->getMessage())) : __('New Data Mapping'));


        return $resultPage;
    }

}
