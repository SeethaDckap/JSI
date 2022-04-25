<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Api;

/**
 * Interface CustomerRepositoryInterface
 *
 * @package Epicor\OrderApproval\Api
 */
interface CustomerRepositoryInterface
{
    /**
     * @param Data\CustomerInterface $customer
     *
     * @return mixed
     */
    public function save(Data\CustomerInterface $customer);

    /**
     * @param Data\CustomerInterface $customer
     *
     * @return mixed
     */
    public function delete(Data\CustomerInterface $customer);
}
