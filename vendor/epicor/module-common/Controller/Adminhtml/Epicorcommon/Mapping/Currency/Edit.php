<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Currency;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Currency
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\CurrencyFactory
     */
    protected $commErpMappingCurrencyFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Erp\Mapping\CurrencyFactory $commErpMappingCurrencyFactory)
    {
        $this->commErpMappingCurrencyFactory = $commErpMappingCurrencyFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commErpMappingCurrencyFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getMagentoId()) {
                $data = $this->_session->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Mapping does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->_registry->register('currency_mapping_data', $model);

        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Mapping "%s"', $model->getErpCode())) : __('New Mapping'));


        return $resultPage;
    }

    }
