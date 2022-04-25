<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Country;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Country
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\CountryFactory
     */
    protected $commErpMappingCountryFactory;


    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Model\Erp\Mapping\CountryFactory $commErpMappingCountryFactory)
    {
        $this->commErpMappingCountryFactory = $commErpMappingCountryFactory;
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commErpMappingCountryFactory->create();
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
        $this->_registry->register('country_mapping_data', $model);
        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Mapping "%s"', $model->getErpCode())) : __('New Mapping'));


        return $resultPage;
    }

    }
