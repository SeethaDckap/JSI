<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Controller\Adminhtml\Boost;

use Epicor\Elasticsearch\Api\Data\BoostInterface;
use Epicor\Elasticsearch\Controller\Adminhtml\AbstractBoost as BoostController;

/**
 * Boost Adminhtml Save controller.
 *
 */
class Save extends BoostController
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $redirectBack = $this->getRequest()->getParam('back', false);
        if ($data) {
            $identifier = $this->getRequest()->getParam(BoostInterface::BOOST_ID);
            $model = $this->boostFactory->create();
            if ($identifier) {
                $model = $this->boostRepository->getById($identifier);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This boost no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }
            if (empty($data['boost_id'])) {
                $data['boost_id'] = null;
            }
            $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);

                return $resultRedirect->setPath('*/*/edit', ['id' => $identifier]);
            }
            $model->setData($data);
            $ruleConditionPost = $this->getRequest()->getParam('rule_condition', []);
            $model->getRuleCondition()->loadPost($ruleConditionPost);
            try {
                $this->boostRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the boost %1.', $model->getName()));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                if ($redirectBack) {
                    $redirectParams = ['id' => $model->getId()];

                    return $resultRedirect->setPath('*/*/edit', $redirectParams);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);

                $returnParams = ['id' => $model->getId()];

                return $resultRedirect->setPath('*/*/edit', $returnParams);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
