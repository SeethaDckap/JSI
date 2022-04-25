<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Plugin\Framework\View;

use Epicor\BranchPickup\Helper\Data;
use Epicor\Lists\Helper\Frontend;
use Magento\Customer\Model\Session;

class Layout
{
    /**
     * @var Data
     */
    protected $branchPickupHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Layout constructor.
     * @param Frontend $frontendHelper
     * @param Data $BranchPickupHelper
     * @param Session $customerSession
     */
    public function __construct(
        Frontend $frontendHelper,
        Data $BranchPickupHelper,
        Session $customerSession
    ) {
        $this->frontendHelper = $frontendHelper;
        $this->branchPickupHelper = $BranchPickupHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\View\Layout $subject
     * @param $result
     * @return bool
     */
    public function afterIsCacheable(
        \Magento\Framework\View\Layout $subject,
        $result
    ) {
        $session = $this->customerSession;
        $customer = $session->getCustomer();

        if($customer){
            $checkCustomer = $customer->getEccIsBranchPickupAllowed();
            $ErpAccount = $customer->getCustomerErpAccount();
            if($ErpAccount && $checkCustomer == "2"){
                $checkErp = $ErpAccount->getIsBranchPickupAllowed();
                if($checkErp && $checkErp != "2"){
                    return false;
                }
            } else if($checkCustomer){
                return false;
            }
        }
        if ($this->branchPickupHelper->checkGlobalBranchPickupAllowed()) {
            return false;
        }
        return $result;
    }


}
