<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Epicor\Comm\Model\Customer\Erpaccount;
use Epicor\OrderApproval\Model\ErpAccountBudgetRepository as ErpAccountBudgetRepository;
use Epicor\OrderApproval\Model\Budget as ModelBudget;
use Epicor\OrderApproval\Model\Groups\Budget;

/**
 * Model Class for OrderApproval
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Ecc Team
 *
 */
class BudgetManagement extends ModelBudget
{
    /**
     * @var string|null
     */
    private $erpAccountId = null;

    /**
     * @var Erpaccount|null
     */
    private $erpAccount = null;

    /**
     * @var Epicor\Dealerconnect\Model\Customer|null
     */
    private $customer = null;

    /**
     * @var array
     */
    private $periods = [];

    /**
     * skip ERP check when Order placing
     * with approval pricess
     *
     * @var bool
     */
    private $isOrder = false;

    /**
     * @param string $groupId
     * @param array $excludeBudgetIds
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteByGroupId($groupId, $excludeBudgetIds = [])
    {
        $this->budgetRepository->deleteByGroupId($groupId, $excludeBudgetIds);
    }

    /**
     * Get Apply Budget for
     * Shopper and ERP level.
     *
     * @param string $grandTotal
     * @param Groups $group
     *
     * @return Budget|false
     */
    public function getApplyBudget($grandTotal, $group)
    {
        /** @var Budget $budget */
        $budget = false;
        //Shopper Budget
        if ($group->getIsBudgetActive()) {
            $budget = $this->getBudget($grandTotal, $group);
        }

        //ERP Budget
        if ((!$budget || $budget->getIsAllowCheckout())
            && $this->getErpAccount()
            && $this->getErpAccount()->getIsBudgetActive()
            && !$this->isOrder
        ) {
            $erpBudget = $this->getBudget($grandTotal, $group, true);
            if ($erpBudget) {
                $budget = $erpBudget;
            }
        }

        return $budget;
    }

    /**
     * @param string $grandTotal
     * @param Groups $group
     * @param false  $isErp
     *
     * @return Budget|false
     */
    public function getBudget($grandTotal, $group, $isErp = false)
    {
        if (!$isErp) {
            $budgetsByType
                = $this->budgetRepository->getByGroupId($group->getId());
        } else {
            /** @var ErpAccountBudgetRepository $erpRepository */
            $erpRepository = $this->erpBudgetRepositoryFactory->create();
            $budgetsByType
                = $erpRepository->getByErpId($this->getErpAccountId());
        }

        $budgets = $this->arrangeBudgetByType($budgetsByType);

        /**
         * GET ERP total based on
         * budget Duration periods.
         */
        if ($isErp) {
            $erpPeriods = $this->getErpPeriods($budgets);
            //Send AST.
            if ($erpPeriods) {
                $this->periods = $this->sendAst($erpPeriods);
            }
        }

        foreach ($this->budgetTypeOrder as $type) {
            if (array_key_exists($type, $budgets)) {
                $isExceedBudget = $this->isBudgetAmountExceed(
                    $grandTotal,
                    $budgets[$type],
                    $isErp
                );

                if ($isExceedBudget) {
                    return $budgets[$type];
                }
            }
        }

        return false;
    }

    /**
     * @param array $erpBudgets
     *
     * @return array
     */
    public function getErpPeriods($erpBudgets)
    {
        $erpDates = [];
        foreach ($this->budgetTypeOrder as $type) {
            if (array_key_exists($type, $erpBudgets)
                && $erpBudgets[$type]["is_erp_include"]
            ) {
                $betweenDates = $this->isBetweenDate(
                    $erpBudgets[$type]['start_date'],
                    $erpBudgets[$type]['duration'],
                    $erpBudgets[$type]['type']
                );

                if ($betweenDates) {
                    $erpDates[] = $this->getErpUTCwithOffset(
                        $betweenDates,
                        $type
                    );
                }
            }
        }

        return $erpDates;
    }


    /**
     * @param \Magento\Framework\DataObject $group
     * @return array
     */
    public function getShopperBudgets($group)
    {
        $budgetsByType = $this->budgetRepository->getByGroupId($group->getGroupId());

        return $this->arrangeBudgetByType($budgetsByType);
    }

    /**
     * Get order amount on between dates.
     *
     * @param string $grandTotal
     * @param array $budget
     * @param bool $isErp
     *
     * @return bool
     */
    public function isBudgetAmountExceed($grandTotal, $budget, $isErp = false)
    {
        $erpAmount = 0;
        $betweenDates = $this->isBetweenDate(
            $budget['start_date'],
            $budget['duration'],
            $budget['type']
        );

        if ($betweenDates) {
            $customerIds = [];
            if ($isErp) {
                $customerIds = $this->budgetRepository->getCustomerIdsByErpId(
                    $this->getErpAccountId()
                );
            } else {
                $customerIds[] = $this->getCustomer()->getId();
            }

            //ERP Order Amount
            if ($this->periods
                && isset($this->periods[$betweenDates['start_date']."_"
                    .$betweenDates['end_date']])
            ) {
                $erpPeriod = $this->periods[$betweenDates['start_date']
                ."_".$betweenDates['end_date']];
                $erpAmount = $erpPeriod->getGrandTotalInc();
            }

            $excludeErp = $budget['is_erp_include'] ? true : false;

            /**
             * Exclude Order amount when Approval is pending and
             * saving history after placed order.
             */
            $orderId = [];
            if ($this->isOrder && $this->isOrder->getEntityId()) {
                $orderId = [$this->isOrder->getEntityId()];
            }

            $totalPastOrderAmount = $this->budgetRepository->getOrderTotalByCustomerId(
                $customerIds,
                $betweenDates,
                $excludeErp,
                $orderId
            );

            $totalOrderAmount = (float)$grandTotal
                + (float)$totalPastOrderAmount
                + (float)$erpAmount;

            $budgetAmount     = $budget['amount'];

            //validate Budget Total vs Order Total
            if ($totalOrderAmount > $budgetAmount) {
                return true;
            }
        }

        return false;

    }

    /**
     * @param \Epicor\Dealerconnect\Model\Customer|null $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @param $erpAccountId
     */
    public function setErpAccountId($erpAccountId)
    {
        $this->erpAccountId = $erpAccountId;
    }

    /**
     * @param Erpaccount|null $erpAccount
     */
    public function setErpAccount($erpAccount)
    {
        $this->erpAccount = $erpAccount;
    }

    /**
     * @return \Epicor\Dealerconnect\Model\Customer|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return string|null
     */
    public function getErpAccountId()
    {
        return $this->erpAccountId;
    }

    /**
     * @return Erpaccount|null
     */
    public function getErpAccount()
    {
        return $this->erpAccount;
    }

    /**
     * @param false $order
     */
    public function isOrder($order=false)
    {
        $this->isOrder = $order;
    }
}
