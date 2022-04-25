<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\GroupSave;

use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Store\Model\ScopeInterface as Scope;
use Epicor\OrderApproval\Model\GroupManagement;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Collection as GroupsCollection;
use Magento\Framework\App\ResourceConnection;
use Epicor\OrderApproval\Model\GroupsRepository;
use Magento\Sales\Api\OrderRepositoryInterface;
use Epicor\OrderApproval\Model\Approval\GroupApproval;
use Epicor\OrderApproval\Model\Approval\EmailSenders;

class Utilities
{
    const ORDER_APPROVAL_ENABLED_CONFIG_PATH = 'ecc_order_approval/global/enabled';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var []
     */
    private $postData;

    /**
     * @var CustomerSessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var ScopeConfig
     */
    private $scopeConfig;

    /**
     * @var GroupsRepository
     */
    private $groupsRepository;

    /**
     * @var GroupApproval
     */
    private $groupApproval;

    /**
     * @var EmailSenders
     */
    private $emailSenders;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Utilities constructor.
     * @param RequestInterface $request
     * @param CustomerSessionFactory $customerSessionFactory
     * @param ScopeConfig $scopeConfig
     * @param GroupsRepository $groupsRepository
     * @param GroupApproval $groupApproval
     * @param EmailSenders $emailSenders
     */
    public function __construct(
        RequestInterface $request,
        CustomerSessionFactory $customerSessionFactory,
        ScopeConfig $scopeConfig,
        GroupsRepository $groupsRepository,
        GroupApproval $groupApproval,
        EmailSenders $emailSenders
    ) {
        $this->request = $request;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->groupsRepository = $groupsRepository;
        $this->groupApproval = $groupApproval;
        $this->emailSenders = $emailSenders;
    }

    /**
     * @return array
     */
    public function getPostData()
    {
        if (!$this->postData) {
            $this->setPostData();
        }
        return $this->postData;
    }

    /**
     * @return ResourceConnection
     */
    public function getResourceConnection()
    {
        return $this->groupApproval->getResourceConnection();
    }

    /**
     * @return GroupManagement
     */
    public function getGroupManagement()
    {
        return $this->groupApproval->getGroupManagement();
    }

    /**
     * @return EmailSenders
     */
    public function getEmailSenders()
    {
        return $this->emailSenders;
    }

    /**
     * @return GroupsRepository
     */
    public function getGroupsRepository()
    {
        return $this->groupsRepository;
    }

    /**
     * @return OrderRepositoryInterface
     */
    public function getOrderRepository()
    {
        return $this->groupApproval->getOrderRepository();
    }

    /**
     * @return mixed
     */
    public function isOrderApprovalActive()
    {
        return $this->scopeConfig->getValue(
            self::ORDER_APPROVAL_ENABLED_CONFIG_PATH,
            Scope::SCOPE_STORE
        );
    }

    /**
     * @return void
     */
    private function setPostData()
    {
        $this->postData = $this->request->getPost();
    }

    /**
     * @return string
     */
    public function getCreatedByEmail()
    {
        return $this->getCustomerSession()->getCustomer()->getEmail();
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        if (!$this->customerSession) {
            $this->customerSession = $this->customerSessionFactory->create();
        }
        return $this->customerSession;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->getCustomerSession()->getCustomer();
    }

    /**
     * @return array
     */
    public function getCustomerGroupIds()
    {
        $customer = $this->getCustomer();
        $customerId = $customer->getEntityId();
        $erpId = $customer->getData('ecc_erpaccount_id');
        $groupCustomers = $this->getGroupManagement()->getGroupByCustomer($customerId, $erpId);
        if ($groupCustomers instanceof GroupsCollection) {
            return $groupCustomers->getAllIds();
        }
    }

    /**
     * @return string
     */
    public function getGroupId()
    {
        $data = $this->getPostData();
        return $data['group_id_val'] ?? '';
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        $customer = $this->getCustomer();

        return $customer->getEntityId();
    }

    /**
     * @return array|mixed|null
     */
    public function getCustomerErpAccountId()
    {
        $customer = $this->getCustomer();

        return $customer->getData('ecc_erpaccount_id');
    }

    /**
     * @return string
     */
    public function isUpdate()
    {
        return $this->getGroupId();
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerData()
    {
        return $this->getCustomerSession()->getCustomerData();
    }

    /**
     * @param $orderId
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface|\Epicor\OrderApproval\Model\Groups
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getGroupFromHistory($orderId)
    {
        $historyId = $this->getHistoryId($orderId);
        if (!$historyId) {
            throw new \InvalidArgumentException('History id is Missing for order: ' . $orderId);
        }
        $connection = $this->getResourceConnection()->getConnection();
        $table = $connection->getTableName('ecc_approval_order_history');
        $sql = "SELECT group_id FROM $table WHERE id = $historyId";
        $groupId = $connection->fetchOne($sql);

        return $this->getGroupsRepository()->getById($groupId);
    }

    /**
     * @param $orderId
     * @return mixed|string
     */
    private function getHistoryId($orderId)
    {
        $historyData = $this->request->getParam('history_data');

        return $historyData[$orderId] ?? '';
    }
}
