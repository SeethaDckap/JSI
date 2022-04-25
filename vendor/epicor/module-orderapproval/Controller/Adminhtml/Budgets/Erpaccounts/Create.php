<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Budgets\Erpaccounts;

use Epicor\OrderApproval\Api\ErpAccountBudgetRepositoryInterface;
use Epicor\OrderApproval\Api\Data\ErpAccountBudgetInterface;
use Epicor\OrderApproval\Model\ErpAccountBudget;
use Epicor\OrderApproval\Model\Budgets\EndDate;
use Magento\Framework\View\Result\Layout as ResultLayout;
use Magento\Backend\Block\Template as BackendTemplate;

class Create extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    /**
     * @var ErpAccountBudgetRepositoryInterface
     */
    private $erpAccountBudgetRepository;

    /**
     * @var EndDate
     */
    private $endDate;

    /**
     * @var ResultLayout
     */
    private $layout;

    /**
     * Create constructor.
     * @param ErpAccountBudgetRepositoryInterface $erpAccountBudgetRepository
     * @param EndDate $endDate
     * @param \Epicor\Comm\Controller\Adminhtml\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     */
    public function __construct(
        ErpAccountBudgetRepositoryInterface $erpAccountBudgetRepository,
        EndDate $endDate,
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct(
            $context,
            $backendAuthSession
        );
        $this->erpAccountBudgetRepository = $erpAccountBudgetRepository;
        $this->endDate = $endDate;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->layout = $this->_resultLayoutFactory->create();
        $this->getResponse()->setBody(
            $this->getBody() . $this->addDatePickerJs()
        );
    }

    /**
     * @return string
     */
    private function getBody()
    {
        try {
            return $this->layout->getLayout()
                ->createBlock('Epicor\OrderApproval\Block\Adminhtml\Budgets\ErpAccounts\Edit\Tab\BudgetForm')
                ->setData('form_data', $this->setFormData())
                ->toHtml();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    private function addDatePickerJs()
    {
        try {
            return $this->layout->getLayout()
                ->createBlock('Magento\Backend\Block\Template')
                ->setTemplate('Epicor_OrderApproval::budgets/erpaccount/tab/datepicker.phtml')
                ->toHtml();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @return array
     */
    private function setFormData()
    {
        $budgetId = $this->getRequest()->getParam('budget_id');
        if ($budgetId) {
            return $this->getBudgetFormData($budgetId);
        } else {
            return [];
        }
    }

    /**
     * @param string $budgetId
     * @return array
     */
    private function getBudgetFormData($budgetId)
    {
        $budgetValues = [];
        /** @var ErpAccountBudget $budget */
        $budget = $this->erpAccountBudgetRepository->getById($budgetId);
        $budgetValues['budget_id'] = $budgetId;
        $budgetValues['erp_orders'] = $budget->getIsErpInclude();
        $budgetValues[ErpAccountBudget::IS_ALLOW_CHECKOUT] = $budget->getIsAllowCheckout();
        $budgetValues[ErpAccountBudget::START_DATE] = $budget->getStartDate();
        $budgetValues['end_date'] = $this->getEndDate($budget);
        $budgetValues[ErpAccountBudget::ERP_ID] = $budget->getErpId();
        $budgetValues['budget_amount'] = number_format($budget->getAmount(), 4) ;
        $budgetValues[ErpAccountBudget::DURATION] = $budget->getDuration();
        $budgetValues[ErpAccountBudget::TYPE] = $budget->getType();

        return $budgetValues;
    }

    /**
     * @param $budget ErpAccountBudget
     * @return false|mixed|string
     */
    private function getEndDate($budget)
    {
        /** @var ErpAccountBudget $budget */
        return $this->endDate->calculateBudgetEndDate(
            $budget->getStartDate(),
            $budget->getDuration(),
            $budget->getType()
        );
    }
}
