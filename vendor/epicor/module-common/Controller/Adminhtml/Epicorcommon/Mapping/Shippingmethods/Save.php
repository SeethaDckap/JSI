<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Shippingmethods;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Shippingmethods
{


    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;



    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory
     */
    protected $commErpMappingShippingmethodFactory;



    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Comm\Helper\Data $commHelper,
        \Epicor\Comm\Model\Erp\Mapping\ShippingmethodFactory $commErpMappingShippingmethodFactory
       )
    {
        $this->commHelper = $commHelper;

        $this->commErpMappingShippingmethodFactory = $commErpMappingShippingmethodFactory;

        parent::__construct($context, $backendAuthSession);
    }


public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $this->commHelper->getShippingmethodList();
            $activeCarriers = $this->_registry->registry('shipping_carriers');
            if ($activeCarriers[$data['shipping_method']]) {          // if shipping method missing, don't try to save 
                $model = $this->commErpMappingShippingmethodFactory->create();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                }
                $model->setData($data);
                $model->setShippingMethod($activeCarriers[$data['shipping_method']]);
                $model->setShippingMethodCode($data['shipping_method']);
                $model->setTrackingUrl($data['tracking_url']);
                $model->setErpCode($data['erp_code']);

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
            } {
                // this will only be fired if a method is removed between selecting it and saving it - not very likely                
                $this->messageManager->addErrorMessage(__('Selected shipping method not available at this time'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('No data found to save'));
        }
        $this->_redirect('*/*/');
    }

    }
