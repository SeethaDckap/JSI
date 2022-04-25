<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Group;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Access\Group
{

    /**
     * @var \Epicor\Common\Model\Access\GroupFactory
     */
    protected $commonAccessGroupFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Epicor\Common\Model\Access\GroupFactory $commonAccessGroupFactory,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->commonAccessGroupFactory = $commonAccessGroupFactory;
        $this->backendSession = $backendSession;
    }
    public function execute()
    {

        if ($data = $this->getRequest()->getPost()) {
            $model = $this->commonAccessGroupFactory->create();
            /* @var $model Epicor_Common_Model_Access_Group */

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            if (!isset($data['erp_account_id']) || empty($data['erp_account_id'])) {
                $model->setErpAccountId(null);
            }

            $this->backendSession->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $model->save();

                if (isset($data['customer_in_group'])) {
                    $this->saveCustomers($data, $model);
                }

                if (isset($data['right_in_group'])) {
                    $this->saveRights($data, $model);
                }

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving Access Group'));
                }

                $this->backendSession->addSuccess(__('Access Group was successfully saved.'));
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
