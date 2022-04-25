<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Budgets\Erpaccounts;

use Epicor\Comm\Controller\Adminhtml\Context;
use Magento\Backend\Model\Auth\Session;
use Epicor\OrderApproval\Api\ErpAccountBudgetRepositoryInterface;
use Epicor\OrderApproval\Api\Data\ErpAccountBudgetInterfaceFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Epicor\OrderApproval\Model\ErpAccountBudget;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Layout;
use Epicor\OrderApproval\Model\Budgets\Utilities as BudgetUtilities;

class Save extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    /**
     * @var ErpAccountBudgetRepositoryInterface
     */
    private $erpAccountBudgetRepository;

    /**
     * @var ErpAccountBudgetInterfaceFactory
     */
    private $erpAccountBudgetFactory;

    /**
     * @var array
     */
    private $postData;

    /**
     * @var ErpAccountBudget
     */
    private $budgetResource;

    /**
     * @var array
     */
    private $budgetData;

    /**
     * @var BudgetUtilities
     */
    private $budgetUtilities;

    /**
     * Save constructor.
     * @param Context $context
     * @param Session $backendAuthSession
     * @param ErpAccountBudgetRepositoryInterface $erpAccountBudgetRepository
     * @param ErpAccountBudgetInterfaceFactory $erpAccountBudgeFactory
     * @param ResultFactory $resultFactory
     * @param BudgetUtilities $budgetUtilities
     */
    public function __construct(
        Context $context,
        Session $backendAuthSession,
        ErpAccountBudgetRepositoryInterface $erpAccountBudgetRepository,
        ErpAccountBudgetInterfaceFactory $erpAccountBudgeFactory,
        ResultFactory $resultFactory,
        BudgetUtilities $budgetUtilities
    ) {
        parent::__construct(
            $context,
            $backendAuthSession
        );
        $this->erpAccountBudgetRepository = $erpAccountBudgetRepository;
        $this->erpAccountBudgetFactory = $erpAccountBudgeFactory;
        $this->resultFactory = $resultFactory;
        $this->budgetUtilities = $budgetUtilities;
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
        }
        $result = $this->resultFactory->create($this->resultFactory::TYPE_JSON);
        $this->postData = $this->getRequest()->getPost();

        $this->budgetResource = $this->getBudgetResource();
        $this->setBudgetData();

        if ($id = $this->budgetResource->getId()) {
            $this->budgetData['id'] = $id;
        }
        $this->budgetResource->setData($this->budgetData);

        try {
            $this->erpAccountBudgetRepository->save($this->budgetResource);
            $result->setData(
                ['success' => 'Saved Budget successfully id: ' . $this->budgetResource->getId()]
            );
        } catch (\Exception $e) {
            $result->setData(
                ['error' => $e->getMessage()]
            );
        }

        return $result;
    }

    /**
     * @throws LocalizedException
     * @return void
     */
    private function setBudgetData()
    {
        $fromDate = $this->postData['from_date'] ?? '';
        $this->budgetData = [
            $this->budgetResource::TYPE => $this->postData['budget_type'] ?? '',
            $this->budgetResource::DURATION => $this->postData['duration'] ?? '',
            $this->budgetResource::AMOUNT => str_replace(',', '', $this->postData['budget_amount'] ?? ''),
            $this->budgetResource::ERP_ID => $this->postData['erp_id'] ?? '',
            $this->budgetResource::START_DATE => $this->budgetUtilities->getUtcDate($fromDate),
            $this->budgetResource::IS_ALLOW_CHECKOUT => $this->postData['budget_action_checkout'] ?? 0,
            $this->budgetResource::IS_ERP_INCLUDE => isset($this->postData['erp_orders']) ? 1 : 0,
        ];
    }

    /**
     * @return mixed
     */
    private function getBudgetResource()
    {
        if ($budgetId = $this->postData['budget_id']) {
            return $this->erpAccountBudgetRepository->getById($budgetId);
        } else {
            /** @var  $budgetModel ErpAccountBudget */
            return $this->erpAccountBudgetFactory->create();
        }
    }
}
