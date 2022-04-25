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
interface ErpAccountBudgetRepositoryInterface
{
    /**
     * @param Data\ErpAccountBudgetInterface $erpAccountBudget
     *
     * @return mixed
     */
    public function save(Data\ErpAccountBudgetInterface $erpAccountBudget);

    /**
     * @param Data\ErpAccountBudgetInterface $erpAccountBudget
     *
     * @return mixed
     */
    public function delete(Data\ErpAccountBudgetInterface $erpAccountBudget);

    /**
     * @param int $erpBudgetId
     *
     * @return mixed
     */
    public function getById($erpBudgetId);
}
