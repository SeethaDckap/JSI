<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Controller
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

declare(strict_types=1);

namespace Epicor\Punchout\Controller\Adminhtml\Connections;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Epicor\Punchout\Controller\Adminhtml\Connections;

/**
 * Manage Connections
 */
class MassStatus extends Connections implements HttpPostActionInterface
{


    /**
     * Mass change status.
     *
     * @return Redirect
     * @throws LocalizedException Exception.
     */
    public function execute()
    {
        $status = (int)$this->getRequest()->getParam('status');
        $collection     = $this->filter->getCollection(
            $this->collectionFactory->create()
        );
        $collectionSize = $collection->getSize();

        foreach ($collection as $connection) {
            $connection = $this->connectionRepository->getById($connection->getId());
            $connection->setIsActive($status);
            $this->connectionRepository->save($connection);
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) status have been change.', $collectionSize)
        );

        $resultRedirect = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );

        return $resultRedirect->setPath('*/*/');

    }//end execute()


}//end class
