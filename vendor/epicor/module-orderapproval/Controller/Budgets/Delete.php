<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Budgets;


use Epicor\OrderApproval\Api\BudgetRepositoryInterface;
use Epicor\OrderApproval\Logger\Logger;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends \Epicor\AccessRight\Controller\Action
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var BudgetRepositoryInterface
     */
    private $budgetRepositoryInterface;

    /**
     * Delete constructor.
     * @param Logger $logger
     * @param BudgetRepositoryInterface $budgetRepositoryInterface
     * @param ResultFactory $resultFactory
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        Logger $logger,
        BudgetRepositoryInterface $budgetRepositoryInterface,
        ResultFactory $resultFactory,
        \Magento\Framework\App\Action\Context $context
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->budgetRepositoryInterface = $budgetRepositoryInterface;
        $this->resultFactory = $resultFactory;
    }


    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
        }
        $result = $this->resultFactory->create($this->resultFactory::TYPE_JSON);
        $budgetId = $this->getRequest()->getParam('id');

        if ($budgetId) {
            try {
                $budget = $this->budgetRepositoryInterface->getById($budgetId);
                $this->budgetRepositoryInterface->delete($budget);
                $result->setData(
                    [
                        'type' => 'success-msg',
                        'message' => 'Deleted Budget id: ' . $budgetId
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
        }
        return $result;
    }
}