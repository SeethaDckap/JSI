<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Controller\Adminhtml\Boost;

use Epicor\Elasticsearch\Controller\Adminhtml\AbstractBoost as BoostController;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Boost Adminhtml Edit controller.
 *
 */
class Edit extends BoostController
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $boostId = (int) $this->getRequest()->getParam('id');
        $boost = null;
        try {
            $boost = $this->boostRepository->getById($boostId);
            $this->coreRegistry->register('current_boost', $boost);
            $resultPage->getConfig()->getTitle()->prepend(__('Edit %1', $boost->getName()));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while editing the boost.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/index');
            return $resultRedirect;
        }
        $resultPage->addBreadcrumb(__('Boost'), __('Boost'));
        return $resultPage;
    }
}
