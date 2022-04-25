<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\BranchPickup\Controller\Pickup;

class SaveLocationQuote extends \Epicor\BranchPickup\Controller\Pickup
{

/* Save Branch pickup location in checkout page */

    /**
     * @var \Epicor\BranchPickup\Helper\Branchpickup
     */
    protected $branchPickupBranchpickupHelper;

    /**
     * @var \Magento\Framework\App\ResponseInterface
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


    public function execute()
    {
        $locationCode = $this->_request->getParam('locationcode');
        $helperBranchLocation = $this->branchPickupBranchpickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Branchpickup */
        $helper = $this->branchPickupHelper;
        /* @var $helper Epicor_BranchPickup_Helper_Data */
        $selectBranch = $helper->selectBranchPickup($locationCode);
        $helperBranchLocation->setBranchLocationFilter($locationCode);
        $result = $helperBranchLocation->saveShippingInQuote($locationCode);
        $this->response->setHeader('Content-type', 'application/json');
        $this->response->setBody(json_encode($result));
    }

    }
