<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\DataMapping;

class Delete extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\DataMapping
{
    /**
     * @var \Epicor\Common\Model\DataMapping
     */
    protected $dataMappingFactory;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Common\Model\DataMappingFactory $dataMappingFactory)
    {
        $this->dataMappingFactory = $dataMappingFactory;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = $this->dataMappingFactory->create();
                $model->load($id);

                $model->delete();
                $this->messageManager->addSuccessMessage(__('The data mapping has been deleted.'));
                $this->_redirect('*/*/index/');
                return;
            }
            catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', array(
                    'id' => $this->getRequest()->getParam('id')
                ));
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find the example to delete.'));
        $this->_redirect('*/*/');
    }

}