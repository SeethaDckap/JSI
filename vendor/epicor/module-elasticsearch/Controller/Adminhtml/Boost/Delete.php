<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Controller\Adminhtml\Boost;

use Epicor\Elasticsearch\Controller\Adminhtml\AbstractBoost as BoostController;

/**
 * Boost Adminhtml Delete controller.
 *
 */
class Delete extends BoostController
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $identifier = $this->getRequest()->getParam('id', false);
        $model = $this->boostFactory->create();
        if ($identifier) {
            $model = $this->boostRepository->getById($identifier);
            if (!$model->getId())
            {
                $this->messageManager->addErrorMessage(__('This boost no longer exists.'));
                return $resultRedirect->setPath('*/*/index');
            }
        }
        try {
            $this->boostRepository->delete($model);
            $this->messageManager->addSuccessMessage(__('You deleted the boost %1.', $model->getName()));

            return $resultRedirect->setPath('*/*/index');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
    }
}
