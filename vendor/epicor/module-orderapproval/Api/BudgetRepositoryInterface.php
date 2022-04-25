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
interface BudgetRepositoryInterface
{
    /**
     * @param Data\BudgetInterface $budget
     *
     * @return mixed
     */
    public function save(Data\BudgetInterface $budget);

    /**
     * @param Data\BudgetInterface $budget
     *
     * @return mixed
     */
    public function delete(Data\BudgetInterface $budget);
}
