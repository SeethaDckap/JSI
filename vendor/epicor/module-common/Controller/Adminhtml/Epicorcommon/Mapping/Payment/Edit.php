<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Payment;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Payment
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\PaymentFactory
     */
    protected $commErpMappingPaymentFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Erp\Mapping\PaymentFactory $commErpMappingPaymentFactory)
    {
        $this->commErpMappingPaymentFactory = $commErpMappingPaymentFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commErpMappingPaymentFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getMagentoCode()) {
                $data = $this->_session->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Mapping does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->_registry->register('payment_mapping_data', $model);

        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Mapping "%s"', $model->getErpCode())) : __('New Mapping'));


        return $resultPage;
    }

    }
