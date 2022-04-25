<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Controller\Adminhtml\Transactionlogs;

use Epicor\Punchout\Controller\Adminhtml\Transactionlogs;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPost;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGet;

/**
 * Class Delete
 */
class Delete extends Transactionlogs implements HttpPost, HttpGet
{


    /**
     * Delete action
     *
     * @return Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $transactionlog = $this->loadEntity($id);
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $transactionlog->getId()) {
            try {
                $conRepository = $this->transactionlogsRepository;
                $conRepository->delete($transactionlog);

                $this->messageManager->addSuccessMessage(__('The Transaction log has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {

                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/view', ['entity_id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a Transaction Log to delete.'));

        return $resultRedirect->setPath('*/*/');

    }
    //end execute()


}//end class
