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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Epicor\Punchout\Controller\Adminhtml\Connections;

/**
 * Manage Connections
 */
class Save extends Connections
{


    /**
     * Save connection.
     *
     * @return ResponseInterface|ResultInterface|void
     * @throws LocalizedException Exception.
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getParams()) {
            $connectionId = isset($data["entity_id"]) ? $data["entity_id"] : null;
            $connection   = $this->loadEntity($connectionId);

            // Extract website_id and store_id out of scope_id
            // scope_id = website_id:store_id
            $tokens = explode(':', $data['scope_id']);
            $data['website_id'] = $tokens[0];
            $data['store_id'] = $tokens[1];

            $connection->setData($data);
            $this->processMappings($connection, $data);

            if (!$connectionId || (isset($data['key_regenerate']) && $data['key_regenerate'] == 1)) {
                $this->generateSecretKey($connection);
            }

            // Save connection.
            try {
                $this->connectionRepository->save($connection);
                $this->messageManager->addSuccessMessage(
                    __('Connection Saved Successfully')
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
                $this->messageManager->addErrorMessage(
                    __('Something went wrong. Please try again later.')
                );
            }

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect(
                    '*/*/edit',
                    ['entity_id' => $connection->getId()]
                );
            } else {
                $this->_redirect('*/*/');
            }
        } else {
            $this->_redirect('*/*/');
        }//end if

    }//end execute()


}//end class
