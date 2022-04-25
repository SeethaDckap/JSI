<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Controller\Adminhtml\Boost;

use Epicor\Elasticsearch\Controller\Adminhtml\AbstractBoost as BoostController;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Boost Adminhtml duplicate controller.
 */
class Duplicate extends BoostController
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $boostId = (int) $this->getRequest()->getParam('id');
        $boost = null;
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $boost = $this->boostRepository->getById($boostId);
            $this->coreRegistry->register('current_boost', $boost);
            $newBoost = $this->boostCopier->copy($boost);
            $this->boostRepository->save($newBoost);
            $this->messageManager->addSuccessMessage(__('You duplicated the boost.'));
            $resultRedirect->setPath('*/*/edit', ['id' => $newBoost->getId()]);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while duplicating the boost.'));
            $resultRedirect->setPath('*/*/index');
        }
        return $resultRedirect;
    }
}
