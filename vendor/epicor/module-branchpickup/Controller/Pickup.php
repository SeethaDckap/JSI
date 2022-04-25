<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller;


/**
 * Branch frontend actions
 *
 * @category   Epicor
 * @package    Epicor_BranchPickup
 * @author     Epicor Websales Team
 */
abstract class Pickup extends  \Epicor\AccessRight\Controller\Action
{


    const FRONTEND_RESOURCE = 'Epicor_Checkout::checkout_branch_pickup';
    /**
     * @var \Epicor\BranchPickup\Helper\Data
     */
    protected $branchPickupHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->branchPickupHelper = $branchPickupHelper;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context
        );
    }


    public function preDispatch()
    {
        parent::preDispatch();
        $helper = $this->branchPickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Data */
        if (!$helper->isBranchPickupAvailable()) {
            $this->customerSession->addError('Branch Pickup not available');
            $this->_redirect('/');
        }
    }
}
