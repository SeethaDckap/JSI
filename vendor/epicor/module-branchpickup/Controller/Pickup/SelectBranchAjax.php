<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

class SelectBranchAjax extends \Epicor\BranchPickup\Controller\Pickup
{

    /**
     * @var \Epicor\BranchPickup\Helper\Branchpickup
     */
    protected $branchPickupBranchpickupHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper
    ) {
        $this->branchPickupBranchpickupHelper = $branchPickupBranchpickupHelper;
        parent::__construct(
            $context,$branchPickupHelper,$customerSession
        );
    }


/**
     * Branch Select Ajax Action
     */
    public function execute()
    {
        $contract = $this->getRequest()->getParam('contract');
        $branch = $this->getRequest()->getParam('branch');
        $helper = $this->branchPickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Data */
        $helperBranchLocation = $this->branchPickupBranchpickupHelper;
        /* @var  Epicor_BranchPickup_Helper_Branchpickup */
        if ($branch && $helper->isValidLocation($branch)) {
            $helper->selectBranchPickup($branch);
            $helperBranchLocation->setBranchLocationFilter($branch);
        }
        if ($contract == -1) {
            $helper->selectBranchPickup(null);
        }
    }

    }
