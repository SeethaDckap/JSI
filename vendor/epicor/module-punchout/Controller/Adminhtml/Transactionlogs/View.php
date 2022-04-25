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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Page;

/**
 * View Log
 */
class View extends Transactionlogs
{

    /**
     * View Action.
     *
     * @return Page
     * @throws LocalizedException Exception.
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $transactionlog = $this->loadEntity($id);
        $resultPage = $this->resultPageFactory->create();

        $title = __('New Connection');
        $date = $this->commHelper->getLocalDate($transactionlog->getStartDatestamp());
        if ($transactionlog->getId()) {
            $title = $transactionlog->getType();
            $title = __("Log entry for ". $title . " at " . " $date");
        }

        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;

    }//end execute()


}//end class
