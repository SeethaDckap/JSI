<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Blog
 */


declare(strict_types=1);

namespace Amasty\Blog\Controller\Adminhtml\Posts;

use Amasty\Blog\Api\Data\PostInterface;
use Amasty\Blog\Api\PostRepositoryInterface;
use Amasty\Blog\Controller\Adminhtml\Traits\SaveTrait;
use Amasty\Blog\Model\Posts;
use Amasty\Blog\Model\Source\PostStatus;

class Save extends \Amasty\Blog\Controller\Adminhtml\Posts
{
    use SaveTrait;

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = (int)$this->getRequest()->getParam('post_id');

            try {
                $inputFilter = new \Zend_Filter_Input([], [], $data);
                $data = $inputFilter->getUnescaped();

                if ($id) {
                    $model = $this->getPostRepository()->getById($id);
                } else {
                    $model = $this->getPostRepository()->getPost();
                }

                $data = $this->prepareData($data);
                $model->addData($data);

                if ($this->isUrlKeyExisted($model->getUrlKey(), $model->getPostId())) {
                    $this->getDataPersistor()->set(Posts::PERSISTENT_NAME, $data);
                    $this->getMessageManager()->addErrorMessage(__('Post with the same url key already exists'));
                    $this->addRedirect($id);

                    return;
                }

                $this->_getSession()->setPageData($data);
                $this->prepareForSave($model);
                $this->getPostRepository()->save($model);

                $this->getMessageManager()->addSuccessMessage(__('You saved the item.'));
                $this->_getSession()->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getPostId()]);

                    return;
                }

                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->getMessageManager()->addErrorMessage($e->getMessage());
                $this->getDataPersistor()->set(Posts::PERSISTENT_NAME, $data);
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

    /**
     * @param array $data
     *
     * @return array
     */
    protected function prepareData(array $data)
    {
        if (!isset($data['categories'])) {
            $data['categories'] = [];
        }
        $data['related_posts_container'] = $data['related_posts_container'] ?? [];
        if (is_array($data['related_posts_container'])) {
            $related = [];

            foreach ($data['related_posts_container'] as $item) {
                $related[] = $item['post_id'];
            }

            $related = implode(',', $related);
            $data['related_post_ids'] = $related;
            unset($data['related_posts_container']);
        }

        return $data;
    }

    /**
     * @param $model
     */
    private function prepareForSave($model)
    {
        $this->prepareImage($model, PostInterface::POST_THUMBNAIL);
        $this->prepareImage($model, PostInterface::LIST_THUMBNAIL);
        $this->prepareStatus($model);

        if (!$model->getUrlKey()) {
            $model->setUrlKey($this->getUrlHelper()->generate($model->getTitle()));
        }
    }

    /**
     * @param $model
     */
    public function prepareStatus($model)
    {
        $currentTimestamp = $this->getTimezone()->scopeTimeStamp();
        $publishedDate = strtotime($model->getPublishedAt());

        if (in_array($model->getStatus(), [PostStatus::STATUS_SCHEDULED, PostStatus::STATUS_ENABLED])) {
            $status = $publishedDate > $currentTimestamp ? PostStatus::STATUS_SCHEDULED : PostStatus::STATUS_ENABLED;
            $model->setStatus($status);
        }

        $publishedDate = $publishedDate ?: $currentTimestamp;
        $model->setPublishedAt($publishedDate);
    }

    /**
     * @param $model
     * @param $imageName
     */
    private function prepareImage($model, $imageName)
    {
        $fileName = $imageName . '_file';
        $thumbnail = $model->getData($fileName);

        if (isset($thumbnail) && is_array($thumbnail)) {
            if (isset($thumbnail[0]['name']) && isset($thumbnail[0]['tmp_name'])) {
                $model->setData($imageName, $thumbnail[0]['name']);
            }
        } else {
            $model->setThumbnail($imageName, null);
        }
    }

    protected function getRepository(): PostRepositoryInterface
    {
        return $this->getPostRepository();
    }
}
