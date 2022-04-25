<?php
/**
 *
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Plugin\Account;

use Magento\Customer\Model\Session;

/**
 * Class EditPost
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends \Epicor\Customerconnect\Plugin\Account\AbstractCuau
{
    /**
     * Change customer email or password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function beforeExecute(\Magento\Customer\Controller\Account\EditPost $subject)
    {
        $postdata= $this->_request->getPostValue();
        $this->preparedata($this->session->getCustomerId(),$postdata);
    }
}
