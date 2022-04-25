<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Products;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Products
{


    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;



    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ProductsFactory
     */
    protected $commErpMappingProductsFactory;



    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\Erp\Mapping\ProductsFactory $commErpMappingProductsFactory
       )
    {
        $this->commHelper = $commHelper;

        $this->commErpMappingProductsFactory = $commErpMappingProductsFactory;

        parent::__construct($context, $backendAuthSession);
    }


    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->commErpMappingProductsFactory->create();
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
            $model->setData($data);
            $model->setProductSku($data['product_sku']);
            $model->setProductUom($data['product_uom']);

            $this->_session->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $model->save();

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving mapping'));
                }

                $this->messageManager->addSuccessMessage(__('Mapping saved successfully'));
                $this->_session->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        } else {
            $this->messageManager->addErrorMessage(__('No data found to save'));
        }
        $this->_redirect('*/*/');
    }

    }
