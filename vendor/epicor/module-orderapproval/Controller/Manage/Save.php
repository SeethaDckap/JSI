<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Manage;

use Magento\Framework\App\ResponseInterface;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Erp\Account\CollectionFactory as GroupCollectionFactory;
use Epicor\OrderApproval\Model\GroupSave\Hierarchy as HierarchySave;
use Epicor\OrderApproval\Model\GroupSave\Rules as RulesSave;
use Epicor\OrderApproval\Model\GroupSave\Groups as GroupSave;
use Epicor\OrderApproval\Model\GroupSave\Utilities as SaveUtilites;
use Epicor\OrderApproval\Model\GroupSave\Customers as SaveCustomers;
use Epicor\OrderApproval\Model\GroupSave\ErpAccount as SaveGroupErpAccount;

class Save extends \Epicor\Customerconnect\Controller\Generic
{
    /**
     * @var \Epicor\OrderApproval\Model\Groups
     */
    private $groups;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Customer\CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Customer
     */
    private $customer;

    /**
     * @var \Epicor\OrderApproval\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Epicor\OrderApproval\Model\Groups\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var int
     */
    private $groupId;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var \Epicor\OrderApproval\Api\GroupsRepositoryInterface
     */
    private $groupsRepository;

    /**
     * @var \Epicor\OrderApproval\Model\ErpAccountRepository
     */
    private $erpAccountRepository;

    /**
     * @var \Epicor\OrderApproval\Model\Groups\Erp\AccountFactory
     */
    private $erpAccountFactory;

    /**
     * @var \Epicor\OrderApproval\Model\ResourceModel\Groups\Erp\Account\CollectionFactory
     */
    private $erpAccountCollectionFactory;

    /**
     * @var \Epicor\OrderApproval\Model\GroupSave\Hierarchy
     */
    private $hierarchySave;

    /**
     * @var RulesSave
     */
    private $rulesSave;

    /**
     * @var GroupSave
     */
    private $groupSave;

    /**
     * @var SaveUtilites
     */
    private $utilities;

    /**
     * @var SaveCustomers
     */
    private $saveCustomers;

    /**
     * @var SaveGroupErpAccount
     */
    private $saveGroupErpAccount;
    
    /**
     * @var \Epicor\OrderApproval\Api\Data\GroupsInterface|\Epicor\OrderApproval\Model\Groups
     */
    private $mainGroup;

    /**
     * Save constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Epicor\OrderApproval\Model\Groups $groups
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Epicor\OrderApproval\Model\ResourceModel\Groups\Customer\CollectionFactory $customerCollectionFactory
     * @param \Epicor\OrderApproval\Api\CustomerRepositoryInterface $customerRepository
     * @param \Epicor\OrderApproval\Model\Groups\CustomerFactory $customerFactory
     * @param \Epicor\OrderApproval\Model\Groups\Customer $customer
     * @param \Epicor\Comm\Model\Serialize\Serializer\Json $serializer
     * @param \Epicor\OrderApproval\Api\GroupsRepositoryInterface $groupsRepository
     * @param \Epicor\OrderApproval\Model\ErpAccountRepository $erpAccountRepository
     * @param \Epicor\OrderApproval\Model\Groups\Erp\AccountFactory $erpAccountFactory
     * @param GroupCollectionFactory $erpAccountCollectionFactory
     * @param HierarchySave $hierarchySave
     * @param RulesSave $rulesSave
     * @param GroupSave $groupSave
     * @param SaveUtilites $utilities
     * @param SaveCustomers $saveCustomers
     * @param SaveGroupErpAccount $saveGroupErpAccount
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Epicor\OrderApproval\Model\Groups $groups,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Epicor\OrderApproval\Model\ResourceModel\Groups\Customer\CollectionFactory $customerCollectionFactory,
        \Epicor\OrderApproval\Api\CustomerRepositoryInterface $customerRepository,
        \Epicor\OrderApproval\Model\Groups\CustomerFactory $customerFactory,
        \Epicor\OrderApproval\Model\Groups\Customer $customer,
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer,
        \Epicor\OrderApproval\Api\GroupsRepositoryInterface $groupsRepository,
        \Epicor\OrderApproval\Model\ErpAccountRepository $erpAccountRepository,
        \Epicor\OrderApproval\Model\Groups\Erp\AccountFactory $erpAccountFactory,
        GroupCollectionFactory $erpAccountCollectionFactory,
        HierarchySave $hierarchySave,
        RulesSave $rulesSave,
        GroupSave $groupSave,
        SaveUtilites $utilities,
        SaveCustomers $saveCustomers,
        SaveGroupErpAccount $saveGroupErpAccount
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $localeResolver,
            $resultPageFactory,
            $resultLayoutFactory
        );
        $this->groups = $groups;
        $this->resourceConnection = $resourceConnection;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customer = $customer;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->serializer = $serializer;
        $this->groupsRepository = $groupsRepository;
        $this->erpAccountRepository = $erpAccountRepository;
        $this->erpAccountFactory = $erpAccountFactory;
        $this->erpAccountCollectionFactory = $erpAccountCollectionFactory;
        $this->hierarchySave = $hierarchySave;
        $this->rulesSave = $rulesSave;
        $this->groupSave = $groupSave;
        $this->utilities = $utilities;
        $this->saveCustomers = $saveCustomers;
        $this->saveGroupErpAccount = $saveGroupErpAccount;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if (!$this->isValidData()) {
            $this->redirect();
            return;
        }
        if (!$this->saveCustomers->isMasterShopper()) {
            $this->messageManager->addErrorMessage('Only master shopper can modify groups');
            $this->redirect();
            return;
        }
        $this->saveGroup();
        $this->redirect();
    }

    /**
     * @return void
     */
    private function saveGroup()
    {
        try {
            $this->mainGroup = $this->groupSave->saveMainGroup();
            $this->groupId = $this->mainGroup->getGroupId();
            $this->saveGroupErpAccount->saveGroupErpAccount();
            $this->hierarchySave->saveHierarchy();
            $this->saveCustomers->saveCustomers();
            $this->rulesSave->saveApprovalLimit();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->messageManager->addSuccessMessage('Group "'. $this->mainGroup->getName() . '" saved successfully');
    }

    /**
     * @return void
     */
    private function redirect()
    {
        if ($this->utilities->isUpdate()) {
            if ($id = $this->utilities->getGroupId()) {
                $this->_redirect('*/*/edit', ['id' => $id]);
            } else {
                $this->_redirect('*/*/');
            }
        } else {
            $this->_redirect('*/*/');
        }
    }


    /**
     * @return bool
     */
    private function isValidData()
    {
        $isValid = true;
        $postData = $this->utilities->getPostData();
        $groupName = $postData['group_name'] ?? '';

        if (!$groupName) {
            $this->messageManager->addErrorMessage('Group name missing, group name is required');
            $isValid = false;
        }
        $ruleData = $postData['approval_limit']['conditions'] ?? '';
        if ($ruleData && is_array($ruleData)) {
            foreach ($ruleData as $rule) {
                $value = $rule['value'] ?? '';
                if (!is_numeric($value)) {
                    $this->messageManager->addErrorMessage('Approval value is the wrong type, type numeric expected');
                    $isValid = false;
                }
            }
        }
        $priority = $postData['priority'] ?? '';
        if ($priority && !is_numeric($priority)) {
            $this->messageManager->addErrorMessage('Priority value is the wrong type, type numeric expected');
            $isValid = false;
        }
        if (!$this->validateChildGroups()) {
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * @return bool
     */
    private function validateChildGroups()
    {
        $postData = $this->utilities->getPostData();
        $parentGroup = $postData['groupselect'] ?? '';
        $childGroups = $postData['child_groups'] ?? '';
        if ($parentGroup && is_array($childGroups) && in_array($parentGroup, $childGroups)) {
            $this->messageManager
                ->addErrorMessage("Parent group ($parentGroup) can not have a child ($parentGroup) of itself");
            return false;
        }

        return true;
    }
}
