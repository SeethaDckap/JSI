<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Controller\Adminhtml\Connections;

use Epicor\Punchout\Controller\Adminhtml\Connections;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPost;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGet;

/**
 * Class Delete
 */
class Delete extends Connections implements HttpPost, HttpGet
{


    /**
     * Delete action
     *
     * @return Redirect
     */
    public function execute()
    {
        $connection = $this->loadEntity();
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $connection->getId()) {
            try {
                $conRepository = $this->connectionRepository;
                $conRepository->delete($connection);

                $this->messageManager->addSuccessMessage(__('The connection has been deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {

                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a connection to delete.'));

        return $resultRedirect->setPath('*/*/');

    }//end execute()


}//end class
