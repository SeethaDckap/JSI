<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Remotelinks;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Remotelinks
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\RemotelinksFactory
     */
    protected $commErpMappingRemotelinksFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Erp\Mapping\RemotelinksFactory $commErpMappingRemotelinksFactory)
    {
        $this->commErpMappingRemotelinksFactory = $commErpMappingRemotelinksFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commErpMappingRemotelinksFactory->create();
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
        $this->_registry->register('remotelinks_mapping_data', $model);

        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Mapping ')) : __('New Mapping'));


        return $resultPage;
    }

    }
