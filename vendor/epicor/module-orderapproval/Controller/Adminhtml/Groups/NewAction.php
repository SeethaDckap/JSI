<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Groups;

use Epicor\OrderApproval\Model\GroupsFactory;

class NewAction extends \Epicor\OrderApproval\Controller\Adminhtml\Groups
{
    /**
     * new Groups action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }


}
