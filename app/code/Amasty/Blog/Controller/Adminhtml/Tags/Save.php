<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Blog
 */


declare(strict_types=1);

namespace Amasty\Blog\Controller\Adminhtml\Tags;

use Amasty\Blog\Api\Data\TagInterface;
use Amasty\Blog\Api\TagRepositoryInterface;
use Amasty\Blog\Controller\Adminhtml\Traits\SaveTrait;
use Amasty\Blog\Model\Tag;

class Save extends \Amasty\Blog\Controller\Adminhtml\Tags
{
    use SaveTrait;

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getPostValue();
            $id = (int)$this->getRequest()->getParam('tag_id');
            try {
                $inputFilter = new \Zend_Filter_Input([], [], $data);
                $data = $inputFilter->getUnescaped();
                if ($id) {
                    $model = $this->getTagRepository()->getById($id);
                    $data = $this->retrieveItemContent($data, $model);
                } else {
                    $model = $this->getTagRepository()->getTagModel();
                }

                $model->addData($data);

                if ($this->isUrlKeyExisted($model->getUrlKey(), $model->getTagId())) {
                    $this->getDataPersistor()->set(Tag::PERSISTENT_NAME, $data);
                    $this->getMessageManager()->addErrorMessage(__('Tag with the same url key already exists'));
                    $this->addRedirect($id);

                    return;
                }

                $this->_getSession()->setPageData($model->getData());
                $this->getTagRepository()->save($model);
                $this->getMessageManager()->addSuccessMessage(__('You saved the item.'));
                $this->_getSession()->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', [
                        'id' => $model->getId(),
                        'store' => (int)$this->getRequest()->getParam('store_id', 0)
                    ]);

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->getMessageManager()->addErrorMessage($e->getMessage());
                $this->getDataPersistor()->set(Tag::PERSISTENT_NAME, $data);
                $this->addRedirect($id);

                return;
            } catch (\Exception $e) {
                $this->getMessageManager()->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->getLogger()->critical($e);
                $this->_getSession()->setPageData($data);
                $this->_redirect('*/*/edit', ['id' => $id]);

                return;
            }
        }
        $this->_redirect('*/*/');
    }

    protected function getRepository(): TagRepositoryInterface
    {
        return $this->getTagRepository();
    }

    /**
     * @return array
     */
    private function getFieldsByStore()
    {
        return TagInterface::FIELDS_BY_STORE;
    }
}
