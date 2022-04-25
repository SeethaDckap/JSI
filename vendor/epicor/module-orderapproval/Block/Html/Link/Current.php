<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Html\Link;

use Magento\Store\Model\ScopeInterface as Scope;
use Epicor\OrderApproval\Model\GroupSave\Utilities as ApprovalGroupsUtilities;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;

class Current extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * Default path
     *
     * @var \Magento\Framework\App\DefaultPathInterface
     */
    protected $_defaultPath;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerCustomerFactory;

    /**
     * @var GroupCustomers
     */
    private $groupCustomers;

    /**
     * Current constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
     * @param GroupCustomers $groupCustomers
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        GroupCustomers $groupCustomers,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->_defaultPath = $defaultPath;
        $this->customerSession = $customerSession;
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->groupCustomers = $groupCustomers;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addLinkConditionally()
    {
        return $this->isLinkActiveForCustomer() && $this->isOrderApprovalActive();
    }

    /**
     * @return mixed
     */
    private function isOrderApprovalActive()
    {
        return $this->_scopeConfig->getValue(
            ApprovalGroupsUtilities::ORDER_APPROVAL_ENABLED_CONFIG_PATH,
            Scope::SCOPE_STORE
        );
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isLinkActiveForCustomer()
    {
        return $this->groupCustomers->isMasterShopperB2B();
    }

    /**
     * @return false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _toHtml()
    {
        if ($this->addLinkConditionally()) {
            return parent::_toHtml();
        } else {
            return false;
        }
    }
}