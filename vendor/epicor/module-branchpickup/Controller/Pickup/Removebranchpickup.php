<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

use Magento\Framework\App\ResponseInterface;

class Removebranchpickup extends \Epicor\BranchPickup\Controller\Pickup
{
    /**
     * @var \Epicor\BranchPickup\Helper\Branchpickup
     */
    protected $branchPickupBranchpickupHelper;

    /**
     * Response interface.
     *
     * @var ResponseInterface
     */
    protected $response;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\BranchPickup\Helper\Data $branchPickupHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\BranchPickup\Helper\Branchpickup $branchPickupBranchpickupHelper

    ) {
        $this->branchPickupBranchpickupHelper = $branchPickupBranchpickupHelper;
        $this->response = $context->getResponse();
        parent::__construct(
            $context,$branchPickupHelper,$customerSession
        );
    }

/**
     * Remove Branch Pickup action in Grid
     * return null
     */
    public function execute()
    {
        $helperBranchLocation = $this->branchPickupBranchpickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Branchpickup */
        $helper = $this->branchPickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Data */
        $helper->selectBranchPickup(null);
        $helper->resetBranchLocationFilter();
        $result = $helperBranchLocation->saveShippingInQuote(null);
        $this->response->setHeader('Content-type', 'application/json');
        $this->response->setBody(json_encode($result));

    }//end execute()


}
