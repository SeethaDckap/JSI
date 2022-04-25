<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\DataMapping;

use Magento\Framework\Exception\AlreadyExistsException;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\DataMapping
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
        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->dataMappingFactory->create();
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
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving mapping'));
                }

                $this->messageManager->addSuccessMessage(__('Mapping was successfully saved.'));
                $this->_session->setFormData(false);

                // The following line decides if it is a "save" or "save and continue"
                $params = array();
                if ($this->getRequest()->getParam('back')) {
                    $params = array('id' => $model->getId());
                    $this->_redirect('*/*/edit', $params);
                } else {
                    $this->_redirect('*/*/',$params);
                }
            } catch (AlreadyExistsException $e) {
                $messageType = $this->getRequest()->getParam('message');
                $orignalTag = $this->getRequest()->getParam('orignal_tag');
                $this->messageManager->addErrorMessage("$messageType + $orignalTag mapped - you cannot duplicate");
                $this->_redirect('*/*/new');
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