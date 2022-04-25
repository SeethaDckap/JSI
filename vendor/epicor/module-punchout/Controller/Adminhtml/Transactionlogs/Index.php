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

namespace Epicor\Punchout\Controller\Adminhtml\Transactionlogs;

use \Epicor\Punchout\Controller\Adminhtml\Transactionlogs;

/**
 * Transaction Logs
 */
class Index extends Transactionlogs
{

    /**
     * Render Transaction Logs listings.
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Transaction Logs')));

        return $resultPage;
    }//end execute()


}//end class
