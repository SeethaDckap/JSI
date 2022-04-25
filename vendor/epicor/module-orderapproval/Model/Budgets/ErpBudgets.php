<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Budgets;

use \Epicor\Comm\Model\Customer\Erpaccount;

class ErpBudgets
{
    /**
     * @var Erpaccount
     */
    private $erpaccount;

    /**
     * ErpBudgets constructor.
     * @param Erpaccount $erpaccount
     */
    public function __construct(
        \Epicor\Comm\Model\Customer\Erpaccount $erpaccount
    ) {
        $this->erpaccount = $erpaccount;
    }

    /**
     * @param string $erpId
     * @return Erpaccount
     */
    public function getErpAccount($erpId)
    {
        return $this->erpaccount->load($erpId);
    }
}