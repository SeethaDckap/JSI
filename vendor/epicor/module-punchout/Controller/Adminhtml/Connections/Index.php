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

/**
 * Manage Connections
 */
class Index extends Connections
{


    /**
     * Render connection listings.
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Manage Connections')));

        return $resultPage;
    }//end execute()


}//end class
