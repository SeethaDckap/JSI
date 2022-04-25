<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Html\Link;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\ScopeInterface as Scope;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;

class Approvals extends \Magento\Framework\View\Element\Html\Link\Current implements SortLinkInterface
{
    const ORDER_APPROVAL_ENABLED_CONFIG_PATH = 'ecc_order_approval/global/enabled';

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
     * Approvals constructor.
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
     * ECC1 method was addLinkToParentBlock
     * @return boolean
     */
    public function addLinkConditionally()
    {
        return $this->groupCustomers->isTypeB2B()  && $this->isOrderApprovalActive();
    }

    /**
     * @return mixed
     */
    private function isOrderApprovalActive()
    {
        return $this->_scopeConfig->getValue(self::ORDER_APPROVAL_ENABLED_CONFIG_PATH, Scope::SCOPE_STORE);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->addLinkConditionally()) {
            return parent::_toHtml();
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}