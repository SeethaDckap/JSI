<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Shippingstatus;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Shippingstatus
{


    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;



    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory
     */
    protected $commErpMappingShippingstatusFactory;



    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\Erp\Mapping\ShippingstatusFactory $commErpMappingShippingstatusFactory
       )
    {
        $this->commHelper = $commHelper;

        $this->commErpMappingShippingstatusFactory = $commErpMappingShippingstatusFactory;

        parent::__construct($context, $backendAuthSession);
    }


public function execute()
    {
                $data = $this->getRequest()->getPostValue();
        if ($data) {
            $model = $this->commErpMappingShippingstatusFactory->create();
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                $erpCode = $model->getShippingStatusCode();
            }
            if (array_key_exists('is_default', $data)) {
                $isDefault = 1;
            } else {
                $isDefault = 0;
            }

            $model->setData($data);
            $model->setData('is_default', $isDefault);

            $this->_session->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                    if ($erpCode != $data['shipping_status_code']) {
                       throw new \Magento\Framework\Exception\LocalizedException(__('Ship status code can not be changed'));
                    }
                } else {
                    $collection = $model->getCollection()->addFieldToFilter('shipping_status_code', array('eq' => $data['shipping_status_code']))->addFieldToFilter('store_id', array('eq' => $data['store_id']))->getFirstItem();
                    if ($collection->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('Ship status code already exist'));
                    }
                }
                $model->save();

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving mapping'));
                }

                $this->messageManager->addSuccessMessage(__($model->getShipStatusCode() . ' Mapping was successfully saved.'));
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
        }
        $this->messageManager->addErrorMessage(__('No data found to save'));
        $this->_redirect('*/*/');
      }

    }
