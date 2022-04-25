<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Api;

/**
 * Interface ErpAccountRepositoryInterface
 *
 * @package Epicor\OrderApproval\Api
 */
interface ErpAccountRepositoryInterface
{
    /**
     * @param Data\ErpAccountInterface $ErpAccount
     *
     * @return mixed
     */
    public function save(Data\ErpAccountInterface $ErpAccount);

    /**
     * @param Data\ErpAccountInterface $ErpAccount
     *
     * @return mixed
     */
    public function delete(Data\ErpAccountInterface $ErpAccount);
}
