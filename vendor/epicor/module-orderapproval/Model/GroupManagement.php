<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Epicor\Comm\Helper\DataFactory as CommHelperFactory;
use Epicor\Comm\Model\Customer\ErpaccountFactory;
use Epicor\Comm\Model\Customer\Erpaccount;
use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;
use Epicor\Common\Helper\Data as commHelper;
use Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory as GroupCollectionFactory;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Collection as GroupCollection;
use Epicor\OrderApproval\Model\HierarchyManagementFactory as HierarchyManagementFactory;
use Epicor\OrderApproval\Model\HierarchyManagement as HierarchyManagement;
use Epicor\OrderApproval\Model\BudgetManagementFactory as BudgetManagementFactory;

/**
 * Model Class for OrderApproval
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Ecc Team
 *
 */
class GroupManagement
{
    /**
     * Self Approved
     */
    const STATUS_SELF_APPROVED = 'Self Approved';

    /**
     * Approved
     */
    const STATUS_APPROVED = 'Approved';

    /**
     * Pending
     */
    const STATUS_PENDING = 'Pending';

    /**
     * Rejected
     */
    const STATUS_REJECTED = 'Rejected';

    /**
     * Skipped
     */
    const STATUS_SKIPPED = 'Skipped';

    /**
     * @var CommHelperFactory
     */
    private $commHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ErpaccountFactory
     */
    private $commCustomerErpAccountFactory;

    /**
     * @var GroupCollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var HierarchyManagementFactory
     */
    private $hierarchyManagementFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var BudgetManagementFactory
     */
    private $budgetManagementFactory;

    /**
     * @var string|null
     */
    private $erpAccountId = null;

    /**
     * @var Epicor\Dealerconnect\Model\Customer|null
     */
    private $customer = null;

    /**
     * @var GroupCollection
     */
    private $groupCollection;

    /**
     * @var array
     */
    private $orderApprovalHistory = [];

    /**
     * @var Groups|null
     */
    private $groupData = null;

    /**
     * @var bool
     */
    private $isBudgetApplied = false;

    /**
     * @var bool
     */
    private $budgetExecuted = false;

    /**
     * @var Erpaccount|null
     */
    private $erpAccount = null;

    /**
     * @var bool
     */
    private $appliedGroup = false;

    /**
     * @var bool
     */
    private $isGroupExecuted = false;

    /**
     * @var null
     */
    private $order = null;

    /**
     * GroupManagement constructor.
     *
     * @param CommHelperFactory           $commHelper
     * @param ScopeConfigInterface        $scopeConfig
     * @param ErpaccountFactory           $commCustomerErpAccountFactory
     * @param GroupCollectionFactory      $groupCollectionFactory
     * @param Serializer                  $serializer
     * @param HierarchyManagementFactory  $hierarchyManagementFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param BudgetManagementFactory     $budgetManagementFactory
     */
    public function __construct(
        CommHelperFactory $commHelper,
        ScopeConfigInterface $scopeConfig,
        ErpaccountFactory $commCustomerErpAccountFactory,
        GroupCollectionFactory $groupCollectionFactory,
        Serializer $serializer,
        HierarchyManagementFactory $hierarchyManagementFactory,
        CustomerRepositoryInterface $customerRepository,
        BudgetManagementFactory $budgetManagementFactory
    ) {
        $this->groupCollectionFactory        = $groupCollectionFactory;
        $this->commCustomerErpAccountFactory = $commCustomerErpAccountFactory;
        $this->commHelper                    = $commHelper;
        $this->scopeConfig                   = $scopeConfig;
        $this->serializer                    = $serializer;
        $this->hierarchyManagementFactory    = $hierarchyManagementFactory;
        $this->customerRepository            = $customerRepository;
        $this->budgetManagementFactory       = $budgetManagementFactory;
    }

    /**
     * @param $customerID
     * @param $erpAccountID
     *
     * @return GroupCollection|false
     */
    public function getGroupByCustomer($customerID, $erpAccountID)
    {
        /** @var GroupCollection $groupCollection */
        $groupCollection = $this->groupCollectionFactory->create();

        $groupCollection->getSelect()->joinLeft(
            array(
                'erp_account' => $groupCollection->getTable(
                    'ecc_approval_group_erp_account'
                ),
            ),
            'main_table.group_id = erp_account.group_id',
            array('erp_account_id')
        );

        $groupCollection->getSelect()->joinLeft(
            array(
                'customer' => $groupCollection->getTable(
                    'ecc_approval_group_customer'
                ),
            ),
            'main_table.group_id = customer.group_id',
            array('customer_id', 'by_group', 'by_customer')
        );

        $where
            = '(`erp_account`.`erp_account_id` = "'
            .$erpAccountID.'" and (`customer`.`customer_id` = "'.$customerID
            .'" OR `customer`.`customer_id` is NULL)) OR (`customer`.`customer_id` = "'
            .$customerID
            .'") OR (`customer`.`customer_id` is NULL and `erp_account`.`erp_account_id` is NULL)';
        $groupCollection->getSelect()->where($where);


        $groupCollection->addFieldtoFilter(
            'main_table.is_active',
            array('eq' => 1)
        );
        $groupCollection->setOrder('main_table.priority', 'DESC');
        $groupCollection->groupById();

        if ($groupCollection->count()) {
            return $groupCollection;
        }

        return false;
    }

    /**
     * Check Config Enable Group.
     *
     * @return mixed
     */
    public function isGroupEnable()
    {
        return $this->scopeConfig->getValue(
            'ecc_order_approval/global/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     *
     * @return Groups|false
     */
    public function getAppliedGroupByQuote(
        \Magento\Quote\Api\Data\CartInterface $quote
    ) {
        if ($quote) {
            if (!$this->appliedGroup && !$this->isGroupExecuted) {
                $grandTotal = $quote->getGrandTotal();
                $this->appliedGroup = $this->getAppliedGroup($grandTotal);
                $this->isGroupExecuted = true;
            }

            return $this->appliedGroup;
        }

        return false;
    }

    /**
     * @param \Epicor\Comm\Model\Order $order
     *
     * @return Groups|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAppliedGroupByOrder(\Epicor\Comm\Model\Order $order)
    {
        if ($order) {
            if (!$this->appliedGroup && !$this->isGroupExecuted) {
                $grandTotal = $order->getGrandTotal();
                $this->appliedGroup = $this->getAppliedGroup(
                    $grandTotal,
                    $order
                );
                $this->isGroupExecuted = true;
            }
            return $this->appliedGroup;
        }

        return false;
    }

    /**
     * @param $order
     *
     * @return bool
     */
    private function isApprovalProcessReset($order)
    {
        if ($order instanceof SalesOrder) {
            return $order->getData('is_order_approval_reset') === '1';
        }

        return false;
    }

    /**
     * @param string $grandTotal
     * @param null   $order
     *
     * @return \Epicor\OrderApproval\Model\Groups|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAppliedGroup($grandTotal, $order = null)
    {
        $this->orderApprovalHistory = [];
        $this->order = $order;
        if (!$this->isGroupEnable()) {
            return false;
        }

        if (!$this->erpAccountId || !$this->customer) {
            /** @var commHelper $commHelper */
            $commHelper = $this->commHelper->create();
            $this->erpAccountId = $commHelper->getErpAccountId();
            $this->customer = $commHelper->getCustomer();
        }

        if ($this->customer instanceof Customer && !$this->customer->getId()
            && $order instanceof SalesOrder
        ) {
            $customerId = $order->getCustomerId();
            $this->customer = $this->customerRepository->getById($customerId);
            $this->erpAccountId
                = $this->customer->getCustomAttribute('ecc_erpaccount_id')
                ->getValue();
        }

        //Exclude SalesRep
        if (!$this->isApprovalProcessReset($order)
            && $this->customer->isSalesRep()
        ) {
            return false;
        }

        if ($this->erpAccountId && $this->customer->getId()) {
            $this->groupCollection = $this->getGroupByCustomer(
                $this->customer->getId(),
                $this->erpAccountId
            );

            if (!$this->groupCollection) {
                return false;
            }

            /** @var Groups $group */
            $group = $this->groupCollection->getFirstItem();
            $this->erpAccount = $this->getErpAccountById($this->erpAccountId);
            $erpType = $this->erpAccount->getAccountType();

            $allowedTypes = array("B2B");
            if (in_array($erpType, $allowedTypes) && $group) {
                $this->setGroupForBudget($group);
                $ruleTotal = $this->getRuleTotal($group);
                $this->applyForHistory($group, self::STATUS_SELF_APPROVED, 0);
                $parentGroup = $this->getParentGroup($group, $grandTotal);
                /**
                 * Auto approve If grand total is
                 * less to rule total
                 */
                if ($grandTotal <= $ruleTotal) {
                    if ($parentGroup
                        && $parentGroup->getId() !== $group->getId()
                    ) {
                        $this->getApplyBudget($grandTotal);
                    }
                    //Apply budget
                    if ((!$this->isBudgetApplied)
                        || ($this->isBudgetApplied
                            && !$this->isBudgetApplied->getIsAllowCheckout())
                    ) {
                        return false;
                    }
                }

                if (!$ruleTotal) {
                    //Apply budget
                    $this->getApplyBudget($grandTotal);
                    if ((!$group->getIsBudgetActive()
                            || !$this->isBudgetApplied)
                        || ($this->isBudgetApplied
                            && !$this->isBudgetApplied->getIsAllowCheckout())
                    ) {
                        return false;
                    }
                }




                //No need approval if same group return by parent
                if ($parentGroup
                    && $parentGroup->getId() == $group->getId()
                ) {
                    if (!$this->isBudgetApplied && !$this->budgetExecuted) {
                        $this->getApplyBudget($grandTotal);
                    }

                    return false;
                }

                if (!$this->budgetExecuted) {
                    $this->getApplyBudget($grandTotal);
                }

                if ($this->isBudgetApplied
                    && !$this->isBudgetApplied->getIsAllowCheckout()
                    && $parentGroup->getId() !== $group->getId()
                ) {
                    return false;
                }

                return $parentGroup;
            }
        }

        return false;
    }


    /**
     * @param Groups $group
     *
     * @return int|mixed
     */
    public function getRuleTotal($group)
    {
        if ($group->getRules()) {
            $conditions = $this->serializer->unserialize($group->getRules());
            if (isset($conditions['conditions'])) {
                foreach ($conditions['conditions'] as $value) {
                    if (isset($value["attribute"])
                        && $value["attribute"] == "total"
                    ) {
                        if (isset($value["value"]) && $value["value"]) {
                            return $value["value"] > 0 ? $value["value"] : 0;
                        }
                    }
                }
            }
        }

        return 0;
    }

    /**
     * @param Groups $group
     * @param int    $grandTotal
     *
     * @return \Epicor\OrderApproval\Model\Groups|false
     */
    public function getParentGroup($group, $grandTotal = 0)
    {
        /** @var HierarchyManagement $linkManagement */
        $linkManagement = $this->hierarchyManagementFactory->create();
        $collection
            = $linkManagement->getCollectionByParentId($group->getGroupId());
        if ($collection->count() == 0) {
            return $group;
        }

        /** @var \Epicor\OrderApproval\Model\Groups $ParentGroup */
        $ParentGroup = $collection->getFirstItem();
        $ruleTotal = $this->getRuleTotal($ParentGroup);

        //Group not active then move to next group
        if (!$this->isGroupActive($ParentGroup)) {
            $this->applyForHistory(
                $ParentGroup,
                self::STATUS_SKIPPED,
                $group->getGroupId()
            );

            return $this->getParentGroup($ParentGroup, $grandTotal);
        }

        //Customer exist on group
        if ($this->groupCollection) {
            $customerExistIds = $this->groupCollection->getAllIds();
            if (in_array($ParentGroup->getId(), $customerExistIds)) {
                if ($grandTotal <= $ruleTotal) {
                    return false;
                }
                $this->applyForHistory(
                    $ParentGroup,
                    self::STATUS_SELF_APPROVED,
                    $group->getGroupId()
                );

                return $this->getParentGroup($ParentGroup, $grandTotal);
            }
        }


        if (!$ParentGroup->getIsMultiLevel()) {
            if ($grandTotal <= $ruleTotal) {
                $this->applyForHistory(
                    $ParentGroup,
                    self::STATUS_PENDING,
                    $group->getGroupId()
                );

                return $ParentGroup;
            } else {
                $this->applyForHistory(
                    $ParentGroup,
                    self::STATUS_SKIPPED,
                    $group->getGroupId()
                );

                return $this->getParentGroup($ParentGroup, $grandTotal);
            }
        }

        $this->applyForHistory(
            $ParentGroup,
            self::STATUS_PENDING,
            $group->getGroupId()
        );

        return $ParentGroup;
    }

    /**
     * @param $group
     *
     * @return int
     */
    public function isGroupActive($group)
    {
        return $group->getIsActive() ? 1 : 0;
    }

    /**
     * @param $group
     *
     * @return int
     */
    public function isMultiLevel($group)
    {
        return $group->getIsMultiLevel() ? 1 : 0;
    }

    /**
     * @param $erpAccountId
     *
     * @return Erpaccount
     */
    public function getErpAccountById($erpAccountId)
    {
        return $this->commCustomerErpAccountFactory->create()
            ->load($erpAccountId);
    }

    /**
     * @param     $group
     * @param int $childGroupID
     * @param     $status
     */
    public function applyForHistory($group, $status, $childGroupID = 0)
    {
        $data = [
            'group_id' => $group->getGroupId(),
            'rules' => $group->getRules(),
            'child_group_id' => $childGroupID,
            'status' => $status,
        ];
        $this->orderApprovalHistory[] = $data;
    }

    /**
     * @return array
     */
    public function getOrderApprovalHistory()
    {
        return $this->orderApprovalHistory;
    }

    /**
     * Set Budget Applied Group.
     *
     * @param Groups $group
     */
    public function setGroupForBudget($group)
    {
        $this->groupData = $group;
    }

    /**
     * Get Budget Applied Group.
     *
     * @return Groups|false
     */
    public function getGroup()
    {
        return $this->groupData ?: false;
    }

    /**
     * Apply Budget.
     *
     * @param $grandTotal
     *
     * @return false|mixed
     */
    public function getApplyBudget($grandTotal)
    {
        /** @var \Epicor\OrderApproval\Model\BudgetManagement $budgetManagement */
        $budgetManagement = $this->budgetManagementFactory->create();
        $budgetManagement->setCustomer($this->customer);
        $budgetManagement->setErpAccountId($this->erpAccountId);
        $budgetManagement->setErpAccount($this->erpAccount);
        if ($this->order) {
            $budgetManagement->isOrder($this->order);
        }

        $this->isBudgetApplied = $budgetManagement->getApplyBudget(
            $grandTotal,
            $this->getGroup()
        );
        $this->budgetExecuted = true;
        return $this->isBudgetApplied;
    }

    /**
     * Applied Budget.
     *
     * @return bool
     */
    public function getAppliedBudget()
    {
        return $this->isBudgetApplied ? : false;
    }
}
