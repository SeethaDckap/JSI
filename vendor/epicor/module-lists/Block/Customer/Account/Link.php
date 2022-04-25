<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account;


/**
 * Customer list management conditional block
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Link extends \Magento\Framework\View\Element\AbstractBlock
{

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Lists\Helper\Frontend\Contract
     */
    protected $listsFrontendContractHelper;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Lists\Helper\Frontend\Contract $listsFrontendContractHelper,
        array $data = []
    ) {
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->customerSession = $customerSession;
        $this->listsFrontendContractHelper = $listsFrontendContractHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function addLinkToParentBlock()
    {
        $parent = $this->getParentBlock();
        $customer = $this->customerCustomerFactory->create()->load($this->customerSession->getId());
        $contractHelper = $this->listsFrontendContractHelper;
        $show = true;
        $eccAccountType = $customer->getEccErpAccountType();
        //Manage Lists should not be available on the Supplier's "My Account" Menu
        if ($eccAccountType == "supplier") {
            $show = false;
        }
        /* @var $contractHelper Epicor_Lists_Helper_Frontend_Contract */
        //&& $contractHelper->contractsEnabled()
        if ($parent && $contractHelper->listsEnabled() && $show) {
            $parent->addLink('List Management', 'lists/list', 'Manage Lists');
            //            $parent->addLink(
            //                'My Contracts', 'lists/contract', 'My Contracts'
            //            );
        }
    }

}
