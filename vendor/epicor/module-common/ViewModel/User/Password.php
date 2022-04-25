<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\ViewModel\User;

use Epicor\Common\Helper\User as UserHelper;

/**
 * Class Password
 * @package Epicor\Common\ViewModel\User
 */
class Password implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var UserHelper
     */
    private $userHelper;

    /**
     * Password constructor.
     * @param UserHelper $userHelper
     */
    public function __construct(
        UserHelper $userHelper
    ) {
        $this->userHelper = $userHelper;
    }

    /**
     * Minimum Password Length
     * @return int
     */
    public function getMinPasswordLength()
    {
        return $this->userHelper->getMinPasswordLength();
    }

    /**
     * Returns Required Character Classes Number
     * @return int
     */
    public function getRequiredClassNumber()
    {
        return $this->userHelper->getRequiredClassNumber();
    }
}