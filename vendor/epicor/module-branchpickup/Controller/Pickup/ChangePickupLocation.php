<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

class ChangePickupLocation extends \Epicor\BranchPickup\Controller\Pickup
{


    /**
     * @var \Epicor\BranchPickup\Model\BranchpickupFactory
     */
    protected $branchPickupBranchpickupFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\BranchPickup\Model\BranchpickupFactory $branchPickupBranchpickupFactory
    ) {

        $this->branchPickupBranchpickupFactory = $branchPickupBranchpickupFactory;
        parent::__construct(
            $context,$branchPickupHelper,$customerSession
        );
    }


/**
     * Change Pickup Action in Checkout Dropdown
     * return json
     */
    public function execute()
    {
        $locationCode = $this->getRequest()->getParam('locationcode');
        $checkProducts = $this->branchPickupBranchpickupFactory->create()->pickupValidation($locationCode);
        /* @var  Epicor_BranchPickup_Model_BranchPickup */
    }

    }
