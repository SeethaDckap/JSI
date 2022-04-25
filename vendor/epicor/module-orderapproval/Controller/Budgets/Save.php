<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Budgets;


use Epicor\OrderApproval\Model\ErpAccountBudget;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Layout;
use Epicor\OrderApproval\Model\BudgetRepository;
use Epicor\OrderApproval\Model\Groups\Budget;
use Epicor\OrderApproval\Api\BudgetRepositoryInterface;
use Epicor\OrderApproval\Model\Budgets\Utilities as BudgetUtilities;
use Epicor\OrderApproval\Api\Data\BudgetInterfaceFactory;

class Save extends \Epicor\AccessRight\Controller\Action
{
    /**
     * @var array
     */
    private $postData;

    /**
     * @var Budget
     */
    private $budgetResource;

    /**
     * @var BudgetRepositoryInterface
     */
    private $budgetRepository;

    /**
     * @var BudgetUtilities
     */
    private $budgetUtilities;

    /**
     * @var array
     */
    private $budgetData;

    /**
     * @var BudgetInterfaceFactory
     */
    private $budgetInterfaceFactory;

    /**
     * Save constructor.
     * @param BudgetRepositoryInterface $budgetRepository
     * @param BudgetUtilities $budgetUtilities
     * @param BudgetInterfaceFactory $budgetInterfaceFactory
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        BudgetRepositoryInterface $budgetRepository,
        BudgetUtilities $budgetUtilities,
        BudgetInterfaceFactory $budgetInterfaceFactory,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->budgetRepository = $budgetRepository;
        $this->budgetUtilities = $budgetUtilities;
        $this->budgetInterfaceFactory = $budgetInterfaceFactory;
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
            $this->budgetRepository->save($this->budgetResource);
            $result->setData(
                [
                    'type' => 'success-msg',
                    'message' => 'Saved Budget successfully id: ' . $this->budgetResource->getId()
                ]
            );
        } catch (\Exception $e) {
            $result->setData(
                [
                    'type' => 'error-msg',
                    'message' => $e->getMessage()
                ]
            );
        }

        return $result;
    }

    /**
     * @return mixed
     */
    private function getBudgetResource()
    {
        if ($budgetId = $this->postData['budget_id']) {
            return $this->budgetRepository->getById($budgetId);
        } else {
            /** @var  $budgetModel Budget */
            return $this->budgetInterfaceFactory->create();
        }
    }

    /**
     * @throws LocalizedException
     * @return void
     */
    private function setBudgetData()
    {
        $startDate = $this->postData['start_date'] ?? '';
        $this->budgetData = [
            $this->budgetResource::TYPE => ucfirst($this->postData['budget_type']) ?? '',
            $this->budgetResource::DURATION => $this->postData['duration'] ?? '',
            $this->budgetResource::AMOUNT => str_replace(',', '', $this->postData['amount'] ?? ''),
            $this->budgetResource::GROUP_ID => $this->postData['group_id'] ?? '',
            $this->budgetResource::START_DATE => $this->budgetUtilities->getUtcDate($startDate),
            $this->budgetResource::IS_ALLOW_CHECKOUT => $this->postData['is_allow_checkout']??'',
            $this->budgetResource::IS_ERP_INCLUDE => isset($this->postData['is_erp_include']) ? 1 : 0,
        ];
    }
}