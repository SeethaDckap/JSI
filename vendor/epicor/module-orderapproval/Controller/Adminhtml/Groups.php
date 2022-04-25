<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml;

use Epicor\Comm\Controller\Adminhtml\Context;
use Magento\Backend\Model\Auth\Session;
use Epicor\OrderApproval\Model\GroupsFactory;
use Epicor\OrderApproval\Model\Groups as GroupsModel;
use Epicor\OrderApproval\Api\GroupsRepositoryInterface;
use Epicor\OrderApproval\Model\ErpManagementFactory as ErpManagementFactory;
use Epicor\OrderApproval\Model\CustomerManagementFactory as CustomerManagementFactory;
use Epicor\OrderApproval\Model\HierarchyManagementFactory as HierarchyManagementFactory;
use Epicor\OrderApproval\Model\BudgetManagementFactory as BudgetManagementFactory;
use Epicor\OrderApproval\Model\ErpManagement as ErpManagement;
use Epicor\OrderApproval\Model\CustomerManagement as CustomerManagement;
use Epicor\OrderApproval\Model\HierarchyManagement as HierarchyManagement;
use Epicor\OrderApproval\Model\BudgetManagement as BudgetManagement;
use Epicor\OrderApproval\Model\RulesFactory;

/**
 * Groups admin actions
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 */
abstract class Groups extends \Epicor\Comm\Controller\Adminhtml\Generic
{
    /**
     * ACL ID
     *
     * @var string
     */
    protected $_aclId = 'Epicor_OrderApproval::groups';

    /**
     * @var GroupsFactory
     */
    protected $groupFactory;

    /**
     * @var GroupsRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var ErpManagementFactory
     */
    private $erpManagementFactory;

    /**
     * @var CustomerManagementFactory
     */
    private $customerManagementFactory;

    /**
     * @var HierarchyManagementFactory
     */
    private $hierarchyManagementFactory;

    /**
     * @var BudgetManagementFactory
     */
    private $budgetManagementFactory;

    /**
     * @var RulesFactory
     */
    private $rulesFactory;

    /**
     * Groups constructor.
     *
     * @param Context                    $context
     * @param Session                    $backendAuthSession
     * @param GroupsFactory              $groupFactory
     * @param GroupsRepositoryInterface  $groupRepository
     * @param ErpManagementFactory       $erpManagement
     * @param CustomerManagementFactory  $customerManagementFactory
     * @param HierarchyManagementFactory $hierarchyManagementFactory
     * @param BudgetManagementFactory    $budgetManagementFactory
     * @param RulesFactory               $rulesFactory
     */
    public function __construct(
        Context $context,
        Session $backendAuthSession,
        GroupsFactory $groupFactory,
        GroupsRepositoryInterface $groupRepository,
        ErpManagementFactory $erpManagement,
        CustomerManagementFactory $customerManagementFactory,
        HierarchyManagementFactory $hierarchyManagementFactory,
        BudgetManagementFactory    $budgetManagementFactory,
        RulesFactory $rulesFactory
    ) {
        $this->groupFactory               = $groupFactory;
        $this->groupRepository            = $groupRepository;
        $this->erpManagementFactory       = $erpManagement;
        $this->customerManagementFactory  = $customerManagementFactory;
        $this->rulesFactory               = $rulesFactory;
        $this->hierarchyManagementFactory = $hierarchyManagementFactory;
        $this->budgetManagementFactory    = $budgetManagementFactory;
        parent::__construct($context, $backendAuthSession);
    }

    /**
     * Admin ACL method
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->backendAuthSession
            ->isAllowed('Epicor_OrderApproval::groups');
    }

    /**
     * @param string|null $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface|GroupsModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function loadEntity($groupId = null)
    {
        $group = null;
        if (!$groupId) {
            $groupId = $this->getRequest()->getParam('group_id', null);
        }

        if ($groupId != null) {
            $group = $this->groupRepository->getById($groupId);
            /* @var $group GroupsModel */
        } else {
            $group = $this->groupFactory->create();
        }

        return $group;
    }

    /**
     * Checks if ERP Accounts Information needs to be saved.
     *
     * @param GroupsModel $group
     * @param array       $data
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function processERPAccountsSave(GroupsModel &$group, array $data)
    {
        if (isset($data['links']['erpaccounts'])) {
            /** @var ErpManagement $erpManagement */
            $erpManagement = $this->erpManagementFactory->create();
            $erpManagement->processERPAccounts($data, $group->getGroupId());
        }
    }

    /**
     * @param GroupsModel $group
     * @param             $data
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function processCustomersSave(GroupsModel &$group, $data)
    {
        if (isset($data['links']['customers'])) {
            /** @var CustomerManagement $customerManagement */
            $customerManagement = $this->customerManagementFactory->create();
            $customerManagement->processCustomers($data, $group->getGroupId());
        }
    }

    /**
     * @param GroupsModel $group
     * @param array       $data
     */
    protected function processHierarchySave(GroupsModel &$group, $data)
    {
        //Save Parent
        if (isset($data['hierarchy']) && isset($data['hierarchy']['parent'])) {
            /** @var HierarchyManagement $hierarchyManagement */
            $hierarchyManagement = $this->hierarchyManagementFactory->create();
            $hierarchyManagement->saveParentHierarchy(
                $data,
                $group->getGroupId()
            );
        }

        //Save children
        if (isset($data['hierarchy']) && isset($data['hierarchy']['children'])) {
            /** @var HierarchyManagement $hierarchyManagement */
            $hierarchyManagement = $this->hierarchyManagementFactory->create();
            $hierarchyManagement->saveChildrenHierarchy(
                $data,
                $group->getGroupId()
            );
        }
    }

    /**
     * @param GroupsModel $group
     * @param array       $data
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    protected function processBudgetSave(GroupsModel &$group, $data)
    {
        /** @var BudgetManagement $budgetManagement */
        $budgetManagement = $this->budgetManagementFactory->create();
        $excludeId = [];
        if (isset($data['budget']) && isset($data['budget']['budget'])) {
            foreach ($data['budget']['budget'] as $budget) {
                if (isset($budget['id'])) {
                    $excludeId[] = $budget['id'];
                }
            }
        }
        $budgetManagement->deleteByGroupId($group->getGroupId(), $excludeId);

        //Save budget
        if (isset($data['budget']) && isset($data['budget']['budget'])) {
            $budgetManagement->saveBudget(
                $data['budget']['budget'],
                $group->getGroupId()
            );
        }

    }//end processBudgetSave()

    /**
     * @return GroupsRepositoryInterface
     */
    protected function getGroupsRepository()
    {
        return $this->groupRepository;
    }

    /**
     * @return RulesFactory
     */
    protected function getRulesModel()
    {
        return $this->rulesFactory;
    }
}
