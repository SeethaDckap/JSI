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

use \Epicor\Punchout\Controller\Adminhtml\Connections;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;

/**
 * Manage Connections
 */
class Edit extends Connections
{


    /**
     * Render connection listings.
     *
     * @return Page
     * @throws LocalizedException Exception.
     */
    public function execute()
    {
        $connection = $this->loadEntity();
        $resultPage = $this->resultPageFactory->create();

        $title = __('New Connection');
        if ($connection->getId()) {
            $title = $connection->getName();
            $title = __('Connection: %1', $title);
        }

        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;

    }//end execute()


}//end class
