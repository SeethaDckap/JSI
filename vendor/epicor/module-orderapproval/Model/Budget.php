<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Epicor\OrderApproval\Api\Data\BudgetInterfaceFactory as BudgetInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Epicor\OrderApproval\Model\Budgets\BudgetTypes;
use Epicor\OrderApproval\Model\BudgetRepository as BudgetRepository;
use Epicor\OrderApproval\Model\ErpAccountBudgetRepositoryFactory as ErpAccountBudgetRepositoryFactory;
use Epicor\Comm\Model\Message\Request\AstFactory;

/**
 * Class Budget
 *
 * @package Epicor\OrderApproval\Model
 */
class Budget
{
    /**
     * @var BudgetInterfaceFactory
     */
    protected $budgetInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var BudgetRepository
     */
    protected $budgetRepository;

    /**
     * @var BudgetTypes
     */
    protected $budgetTypes;

    /**
     * @var ErpAccountBudgetRepositoryFactory
     */
    protected $erpBudgetRepositoryFactory;

    /**
     * @var AstFactory
     */
    protected $commMessageRequestAstFactory;

    /**
     * @var array|null
     */
    protected $erpOrderResponce;

    /**
     * @var array
     */
    protected $budgetTypeOrder = [
        BudgetTypes::BUDGET_TYPE_DAILY,
        BudgetTypes::BUDGET_TYPE_MONTHLY,
        BudgetTypes::BUDGET_TYPE_QUARTERLY,
        BudgetTypes::BUDGET_TYPE_YEARLY
    ];

    /**
     * BudgetManagement constructor.
     *
     * @param BudgetInterfaceFactory            $budgetInterfaceFactory
     * @param BudgetRepository                  $budgetRepository
     * @param DataObjectHelper                  $dataObjectHelper
     * @param BudgetTypes                       $budgetTypes
     * @param ErpAccountBudgetRepositoryFactory $erpBudgetRepositoryFactory
     * @param AstFactory                        $commMessageRequestAstFactory
     */
    public function __construct(
        BudgetInterfaceFactory $budgetInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        BudgetRepository $budgetRepository,
        BudgetTypes $budgetTypes,
        ErpAccountBudgetRepositoryFactory $erpBudgetRepositoryFactory,
        AstFactory $commMessageRequestAstFactory
    ) {
        $this->budgetInterfaceFactory      = $budgetInterfaceFactory;
        $this->dataObjectHelper            = $dataObjectHelper;
        $this->budgetRepository            = $budgetRepository;
        $this->budgetTypes                 = $budgetTypes;
        $this->erpBudgetRepositoryFactory  = $erpBudgetRepositoryFactory;
        $this->commMessageRequestAstFactory= $commMessageRequestAstFactory;
    }

    /**
     * @param array  $data
     * @param string $groupId
     */
    public function saveBudget($data, $groupId)
    {
        foreach ($data as $budgets) {
            /** @var Budget $budget */
            $budget = $this->budgetInterfaceFactory->create();
            $dataArray = [
                \Epicor\OrderApproval\Api\Data\BudgetInterface::GROUP_ID          => $groupId,
                \Epicor\OrderApproval\Api\Data\BudgetInterface::TYPE              => $budgets["type"],
                \Epicor\OrderApproval\Api\Data\BudgetInterface::START_DATE        => $budgets["start_date"],
                \Epicor\OrderApproval\Api\Data\BudgetInterface::DURATION          => $budgets["duration"],
                \Epicor\OrderApproval\Api\Data\BudgetInterface::AMOUNT            => $budgets["amount"],
                \Epicor\OrderApproval\Api\Data\BudgetInterface::IS_ERP_INCLUDE    => isset($budgets["is_erp_include"])
                    ? $budgets["is_erp_include"] : 0,
                \Epicor\OrderApproval\Api\Data\BudgetInterface::IS_ALLOW_CHECKOUT => $budgets["is_allow_checkout"],
            ];

            //update Record
            if (isset($budgets["id"])) {
                $dataArray[\Epicor\OrderApproval\Api\Data\BudgetInterface::ID]
                    = $budgets["id"];
            }

            $this->dataObjectHelper->populateWithArray(
                $budget,
                $dataArray,
                \Epicor\OrderApproval\Api\Data\LinkInterface::class
            );
            $this->budgetRepository->save($budget);
        }
    }

    /**
     * Get End Date.
     *
     * @param string $startDate
     * @param string $duration
     * @param string $budgetType
     *
     * @return false|string
     */
    public function getEndDate($startDate, $duration, $budgetType)
    {
        $endDate = $this->budgetTypes->calculateSavedEndDate(
            $startDate,
            $duration,
            ucfirst($budgetType)
        );

        $formatDate = strtotime($endDate);

        return date('Y-m-d', $formatDate);
    }

    /**
     * Is Between Date.
     *
     * @param string $startDate
     * @param string $duration
     * @param string $budgetType
     *
     * @return array|false
     */
    public function isBetweenDate($startDate, $duration, $budgetType)
    {
        $endDate = $startDate;
        $todayDate = date('Y-m-d', strtotime(date('Y-m-d')));
        $startDate = date('Y-m-d', strtotime($startDate));

        //daily budget only
        $betweenDate = $this->getBetweenDailyDate(
            $startDate,
            $endDate,
            $todayDate,
            $duration,
            $budgetType
        );

        //today date is before start date
        if ($todayDate < $startDate) {
            return false;
        }

        //All other type of budget only
        if (!$betweenDate) {
            $betweenDate = $this->getBetweenOthersDate(
                $startDate,
                $endDate,
                $todayDate,
                $duration,
                $budgetType
            );
        }

        return $betweenDate;
    }

    /**
     * get Start and END Date for
     * Daily budget type.
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $todayDate
     * @param string $duration
     * @param string $budgetType
     *
     * @return array|false
     */
    public function getBetweenDailyDate($startDate, $endDate, $todayDate, $duration, $budgetType)
    {
        //For daily budget only
        if (ucfirst($budgetType) == BudgetTypes::BUDGET_TYPE_DAILY) {
            $endDate = $this->getEndDate($endDate, $duration, $budgetType);
            if ($todayDate >= $startDate && $todayDate <= $endDate) {
                return [
                    'start_date' => $todayDate,
                    'end_date'   => $todayDate,
                ];
            }
        }

        return false;
    }

    /**
     *
     * get Start and END Date for
     * All other type (except daily)
     * of budget.
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $todayDate
     * @param string $duration
     * @param string $budgetType
     *
     * @return array|false
     */
    public function getBetweenOthersDate($startDate, $endDate, $todayDate, $duration, $budgetType)
    {
        for ($i = 1; $i <= $duration; $i++) {
            $endDate = $this->getEndDate($endDate, 1, $budgetType);
            $endDateFinal = date('Y-m-d', strtotime($endDate." -1 day"));
            if ($todayDate <= $endDateFinal) {
                return [
                    'start_date' => $startDate,
                    'end_date'   => $endDateFinal,
                ];
            }

            $startDate = $endDate;
        }

        return false;
    }


    /**
     * Arrange Budget By Type.
     *
     * @param array $budgets
     *
     * @return array
     */
    public function arrangeBudgetByType($budgets)
    {
        $item = [];
        foreach ($budgets as $budget) {
            $item[ucfirst($budget->getType())] = $budget;
        }

        return $item;
    }

    /**
     * Get UTC time format to
     * send date in to AST.
     *
     * @param string $betweenDates
     * @param string $type
     *
     * @return array
     */
    public function getErpUTCwithOffset($betweenDates, $type)
    {
        $startDate = $betweenDates['start_date']. "T00:00:00+00:00";
        $endDate = $betweenDates['end_date']. "T00:00:00+00:00";

        return  [
            'periodFrom' => $startDate,
            'periodTo'   => $endDate,
        ];
    }

    /**
     * Send AST Message.
     *
     * @param array $periods
     *
     * @return array|bool|null
     */
    public function sendAst($periods)
    {
        if (!$this->erpOrderResponce) {
            $ast = $this->commMessageRequestAstFactory->create();
            $ast->setPeriods($periods);
            $ast->sendMessage();
            $this->erpOrderResponce = $ast->getResponsePeriods();

            return $this->erpOrderResponce;
        }

        return $this->erpOrderResponce;

    }
}
