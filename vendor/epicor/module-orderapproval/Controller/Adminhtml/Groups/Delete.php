<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Groups;

use Epicor\OrderApproval\Controller\Adminhtml\Groups;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPost;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGet;

/**
 * Class Delete
 *
 * @package Epicor\OrderApproval\Controller\Adminhtml\Groups
 */
class Delete extends Groups implements HttpPost, HttpGet
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Epicor_OrderApproval::groups';

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $group = $this->loadEntity();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $group->getId();
        if ($group->getId()) {
            $title = "";
            try {
                $title = $group->getName();
                $groupRepository = $this->getGroupsRepository();
                $groupRepository->delete($group);

                // display success message
                $this->messageManager->addSuccessMessage(__('The group has been deleted.'));

                // go to grid
                $this->_eventManager->dispatch('adminhtml_orderapproval_groups_on_delete',
                    [
                        'title' => $title,
                        'status' => 'success',
                    ]);

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_orderapproval_groups_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());

                // go back to edit form
                return $resultRedirect->setPath('*/*/edit',
                    ['group_id' => $id]);
            }
        }

        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a group to delete.'));

        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
