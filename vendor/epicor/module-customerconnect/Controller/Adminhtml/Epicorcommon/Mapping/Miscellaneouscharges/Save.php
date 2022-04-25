<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Miscellaneouscharges;

class Save extends \Epicor\Customerconnect\Controller\Adminhtml\Epicorcommon\Mapping\Miscellaneouscharges
{

    /**
     * @var \Epicor\Customerconnect\Model\Erp\Mapping\MiscellaneouschargesFactory
     */
    protected $erpMappingMiscFactory;

    /**
     * @var \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Miscellaneouscharges\CollectionFactory
     */
    protected $resourceErpMappingMiscCollectionFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Customerconnect\Model\Erp\Mapping\MiscellaneouschargesFactory $erpMappingMiscFactory,
        \Epicor\Customerconnect\Model\ResourceModel\Erp\Mapping\Miscellaneouscharges\CollectionFactory $resourceErpMappingMiscCollectionFactory)
    {
        $this->erpMappingMiscFactory = $erpMappingMiscFactory;
        $this->resourceErpMappingMiscCollectionFactory = $resourceErpMappingMiscCollectionFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $erpCode = $this->getRequest()->getParam('erp_code');
            $collection = $this->resourceErpMappingMiscCollectionFactory->create();
            $existing = $collection->addFieldToFilter('erp_code', array('eq' => $erpCode))->getFirstItem();
            if($existing->getId()){
                $this->messageManager->addErrorMessage(__('Miscellaneous Charge Code already exists.'));
                $this->_redirect('*/*/');
                return;
            }
            $model = $this->erpMappingMiscFactory->create();
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            $this->_session->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $model->save();

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving mapping.'));
                }

                $this->messageManager->addSuccessMessage(__('Mapping was successfully saved.'));
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
