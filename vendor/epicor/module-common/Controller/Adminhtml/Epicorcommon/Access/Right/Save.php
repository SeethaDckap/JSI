<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Right;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Right
{

    /**
     * @var \Epicor\Common\Model\Access\RightFactory
     */
    protected $commonAccessRightFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Epicor\Common\Model\Access\RightFactory $commonAccessRightFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->commonAccessRightFactory = $commonAccessRightFactory;
        $this->backendSession = $backendSession;
    }
    public function execute()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = $this->commonAccessRightFactory->create();
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            $this->backendSession->setFormData($data);
            try {
                if ($id) {
                    $model->setEntityId($id);
                }
                $model->save();

                if (isset($data['group_in_right'])) {
                    $this->saveGroups($data, $model);
                }

                if (isset($data['element_in_right'])) {
                    $this->saveElements($data, $model);
                }

                if (!$model->getEntityId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving Access Right'));
                }

                $this->backendSession->addSuccess(__('Access Right was successfully saved.'));
                $this->backendSession->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            } catch (\Exception $e) {
                $this->backendSession->addError($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        $this->backendSession->addError(__('No data found to save'));
        $this->_redirect('*/*/');
    }

    }
