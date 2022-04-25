<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\B2b\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class User
 * @package Epicor\B2b\Model
 */
class User  extends AbstractModel
{
    /**
     * Initialize user model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Epicor\B2b\Model\ResourceModel\User::class);
    }
}