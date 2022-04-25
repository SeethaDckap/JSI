<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Blog
 */


declare(strict_types=1);

namespace Amasty\Blog\Controller\Adminhtml\Authors;

use Amasty\Blog\Api\AuthorRepositoryInterface;
use Amasty\Blog\Api\Data\AuthorInterface;
use Amasty\Blog\Controller\Adminhtml\Traits\SaveTrait;
use Amasty\Blog\Model\Tag;

class Save extends \Amasty\Blog\Controller\Adminhtml\Authors
{
    use SaveTrait;

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getPostValue();
            $id = (int)$this->getRequest()->getParam('author_id');
            try {
                $inputFilter = new \Zend_Filter_Input([], [], $data);
                $data = $inputFilter->getUnescaped();
                if ($id) {
                    $model = $this->getAuthorRepository()->getById($id);
                    $data = $this->retrieveItemContent($data, $model);
                } else {
                    $model = $this->getAuthorRepository()->getAuthorModel();
                }

                $model->addData($data);

                if ($this->isUrlKeyExisted($model->getUrlKey(), $model->getAuthorId())) {
                    $this->getDataPersistor()->set(Tag::PERSISTENT_NAME, $data);
                    $this->getMessageManager()->addErrorMessage(__('Author with the same url key already exists'));
                    $this->addRedirect($id);

                    return;
                }

                $this->_getSession()->setPageData($model->getData());
                $this->getAuthorRepository()->save($model);
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

    protected function getRepository(): AuthorRepositoryInterface
    {
        return $this->getAuthorRepository();
    }

    /**
     * @return array
     */
    private function getFieldsByStore()
    {
        return AuthorInterface::FIELDS_BY_STORE;
    }
}
