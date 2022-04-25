<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Api\ViewModel\Form;

/**
 * Interface FormKeyInterface
 * @package Epicor\Comm
 */
interface FormKeyInterface
{
    /**
     * Get CSRF form key for templates.
     *
     * @return string
     */
    public function getFormKey();
}
