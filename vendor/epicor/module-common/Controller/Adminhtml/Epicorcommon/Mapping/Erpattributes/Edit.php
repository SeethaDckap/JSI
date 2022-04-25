<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erpattributes;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Erpattributes
{

/*
     * called by admin/epicorcomm_mapping_erpattributes/edit 
     * Blocks defined in adminhtml_epicorcomm_mapping_erpattributes_edit 
     */

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\AttributesFactory
     */
    protected $commErpMappingAttributesFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Epicor\Comm\Model\Erp\Mapping\AttributesFactory $commErpMappingAttributesFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->commErpMappingAttributesFactory = $commErpMappingAttributesFactory;
        $this->backendSession = $backendAuthSession;
        $this->registry = $context->getRegistry();
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commErpMappingAttributesFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getId()) {
                $data = $this->backendSession->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->backendSession->addError(__('Attribute Type does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->registry->register('erpattributes_mapping_data', $model);


        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Mapping "%s"', $model->getErpCode())) : __('New Mapping'));


        return $resultPage;
    }

    }
