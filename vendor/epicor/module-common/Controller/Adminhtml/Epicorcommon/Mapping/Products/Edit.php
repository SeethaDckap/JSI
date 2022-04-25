<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Products;

class Edit extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Products
{


    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ProductsFactory
     */
    protected $commErpMappingProductsFactory;
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;


    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Comm\Model\Erp\Mapping\ProductsFactory $commErpMappingProductsFactory)
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->commErpMappingProductsFactory = $commErpMappingProductsFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $model = $this->commErpMappingProductsFactory->create();
        if ($id) {
            $model->load($id);
            if ($model->getProductSku()) {
                $data = $this->_session->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                $this->messageManager->addErrorMessage(__('Mapping does not exist'));
                $this->_redirect('*/*/');
            }
        }
        $this->_registry->register('products_mapping_data', $model);
        $resultPage = $this->_resultPageFactory->create();

        $resultPage->setActiveMenu('Epicor_Comm::mapping');
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __(sprintf('Edit Mapping "%s"', $model->getProductSku())) : __('New Mapping'));


        return $resultPage;
    }

    }