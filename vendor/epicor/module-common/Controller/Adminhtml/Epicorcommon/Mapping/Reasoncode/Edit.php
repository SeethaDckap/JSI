<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Reasoncode;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Reasoncode
{

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\ReasoncodeFactory
     */
    protected $customerconnectErpMappingReasoncodeFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Customerconnect\Model\Erp\Mapping\ReasoncodeFactory $customerconnectErpMappingReasoncodeFactory)
    {
        $this->customerconnectErpMappingReasoncodeFactory = $customerconnectErpMappingReasoncodeFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->customerconnectErpMappingReasoncodeFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getCode()) {
                //M1 > M2 Translation Begin (Rule 55)
                //$this->_title($this->__('Edit Mapping "%s"', $model->getCode()));
                //M1 > M2 Translation End
                $data = $this->_session->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Mapping does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->_registry->register('reasoncode_mapping_data', $model);
        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Mapping "%s"', $model->getCode())) : __('New Mapping'));


        return $resultPage;
    }

    }
