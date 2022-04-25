<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Budgets\Erpaccounts;

use Epicor\OrderApproval\Api\ErpAccountBudgetRepositoryInterface;
use Magento\Framework\App\ResponseInterface;
use Epicor\OrderApproval\Logger\Logger;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    /**
     * @var ErpAccountBudgetRepositoryInterface
     */
    private $erpAccountBudgetRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Delete constructor.
     * @param Logger $logger
     * @param ErpAccountBudgetRepositoryInterface $erpAccountBudgetRepository
     * @param ResultFactory $resultFactory
     * @param \Epicor\Comm\Controller\Adminhtml\Context $context
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     */
    public function __construct(
        Logger $logger,
        ErpAccountBudgetRepositoryInterface $erpAccountBudgetRepository,
        ResultFactory $resultFactory,
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        parent::__construct(
            $context,
            $backendAuthSession
        );
        $this->erpAccountBudgetRepository = $erpAccountBudgetRepository;
        $this->logger = $logger;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
        }
        $result = $this->resultFactory->create($this->resultFactory::TYPE_JSON);
        $budgetId = $this->getRequest()->getParam('budget_id');

        if ($budgetId) {
            try {
                $budget = $this->erpAccountBudgetRepository->getById($budgetId);
                $this->erpAccountBudgetRepository->delete($budget);
                $result->setData(
                    ['success' => 'Deleted Budget id: ' . $budgetId]
                );
            } catch (\Exception $e) {
                $result->setData(
                    ['error' => $e->getMessage()]
                );
            }
        }
        return $result;
    }
}
