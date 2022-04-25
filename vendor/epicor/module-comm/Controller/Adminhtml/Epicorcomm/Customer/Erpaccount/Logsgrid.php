<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount;

class Logsgrid extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Customer\Erpaccount
{

    public function execute()
    {
        $layout = $this->_resultLayoutFactory->create();
        $this->getResponse()->setBody(
            $layout->getLayout()->createBlock('Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Log')->toHtml()
        );
    }

}
