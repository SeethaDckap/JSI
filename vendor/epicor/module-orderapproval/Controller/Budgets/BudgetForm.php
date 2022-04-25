<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Budgets;

use Epicor\OrderApproval\Logger\Logger;
use Epicor\OrderApproval\Model\BudgetRepository;
use Epicor\OrderApproval\Model\Budgets\Utilities;
use Epicor\OrderApproval\Model\ErpAccountBudget;
use Epicor\OrderApproval\Model\Groups\Budget;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Epicor\OrderApproval\Model\Budgets\EndDate;
use Epicor\OrderApproval\Model\Config\Budgets\Source\BudgetTypes;

class BudgetForm extends \Epicor\AccessRight\Controller\Action
{
    /**
     * @var BudgetRepository
     */
    private $budgetRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EndDate
     */
    private $endDate;

    /**
     * @var BudgetTypes
     */
    private $budgetTypes;

    /**
     * BudgetForm constructor.
     * @param BudgetTypes $budgetTypes
     * @param Logger $logger
     * @param EndDate $endDate
     * @param BudgetRepository $budgetRepository
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        BudgetTypes $budgetTypes,
        Logger $logger,
        EndDate $endDate,
        BudgetRepository $budgetRepository,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->budgetRepository = $budgetRepository;
        $this->logger = $logger;
        $this->endDate = $endDate;
        $this->budgetTypes = $budgetTypes;
    }

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    private $layout;

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->layout = $this->_view->loadLayout();
        $this->getResponse()->setBody(
            $this->getBudgetForm() . $this->getDatePicker()
        );
    }

    /**
     * @return mixed
     */
    private function getBudgetForm()
    {
        $budgetForm = $this->layout->getLayout()
            ->createBlock('Epicor\OrderApproval\Block\Group\Budgets\Form');
        if ($budgetId = $this->getBudgetId()) {
            $budgetForm->setFormData($this->getFormData($budgetId));
        }
        try {
            $isEdit = $this->getRequest()->getParam('is_edit');
            $groupId = $this->getRequest()->getParam('group_id');
            $remainingOptions = count($this->budgetTypes->getRemainingShopperOptions($groupId));
            if ($remainingOptions > 0 || $isEdit) {
                return $budgetForm->toHtml();
            } else {
                return 'limit-exceeded';
            }
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    private function getDatePicker()
    {
        try {
            return $this->layout->getLayout()
                ->createBlock('Magento\Backend\Block\Template')
                ->setTemplate('Epicor_OrderApproval::budgets/tab/datepicker.phtml')
                ->toHtml();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    private function getBudgetId()
    {
        return $this->getRequest()->getParam('budget_id');
    }

    /**
     * @param string $budgetId
     * @return array
     */
    private function getFormData($budgetId)
    {
        try {
            $budget = $this->budgetRepository->getById($budgetId);
            return [
                Budget::TYPE => $budget->getData(Budget::TYPE),
                Budget::GROUP_ID => $budget->getData(Budget::GROUP_ID),
                Budget::AMOUNT => Utilities::getAmountFourPlaceDecimal($budget->getData(Budget::AMOUNT)),
                Budget::DURATION => $budget->getData(Budget::DURATION),
                Budget::ID => $budget->getData(Budget::ID),
                'end_date' => $this->getEndDate($budget),
                Budget::START_DATE => $budget->getData(Budget::START_DATE),
                Budget::IS_ALLOW_CHECKOUT => $budget->getData(Budget::IS_ALLOW_CHECKOUT),
                Budget::IS_ERP_INCLUDE => $budget->getData(Budget::IS_ERP_INCLUDE),
            ];
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
        }
    }

    /**
     * @param Budget $budget
     * @return false|mixed|string
     */
    private function getEndDate($budget)
    {
        /** @var Budget $budget */
        return $this->endDate->calculateBudgetEndDate(
            $budget->getStartDate(),
            $budget->getDuration(),
            $budget->getType()
        );
    }

}