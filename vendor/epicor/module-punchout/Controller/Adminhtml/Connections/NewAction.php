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

use Epicor\Punchout\Controller\Adminhtml\Connections;

/**
 * Manage Connections
 */
class NewAction extends Connections
{


    /**
     * Render connection listings.
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');

    }//end execute()


}//end class
