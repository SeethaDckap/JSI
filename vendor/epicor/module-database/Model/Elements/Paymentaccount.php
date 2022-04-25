<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Database\Model\Elements;

use Magento\Framework\Model\AbstractModel;

class Paymentaccount extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Epicor\Database\Model\ResourceModel\Elements\Paymentaccount');
    }

}