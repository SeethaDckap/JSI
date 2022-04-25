<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Comm
 * @subpackage Plugin
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Plugin\Customer;

use Epicor\Comm\Helper\Messaging;
use Epicor\Comm\Model\Customer\Erpaccount\Address;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;

/**
 * Plugin for customer AccountManagement
 */
class AccountManagementPlugin
{

    /**
     * Helper for Comm messaging
     *
     * @var Messaging
     */
    protected $commMessagingHelper;

    /**
     * Customer session model
     *
     * @var Session
     */
    protected $customerSession;


    /**
     * AccountManagementPlugin constructor.
     *
     * @param Messaging $commMessagingHelper Comm messaging helper.
     * @param Session   $customerSession     Customer session model.
     */
    public function __construct(
        Messaging $commMessagingHelper,
        Session $customerSession
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->customerSession     = $customerSession;

    }//end __construct()


    /**
     * Retrieve default billing address for the given customerId.
     *
     * @param AccountManagement $subject    Customer AccountManagement model.
     * @param \Closure          $proceed    Parent function.
     * @param integer           $customerId Customer ID.
     *
     * @return Address|\Magento\Customer\Model\Address|mixed
     */
    public function aroundGetDefaultBillingAddress(
        AccountManagement $subject,
        \Closure $proceed,
        int $customerId
    ) {
        $masqueradeAccountId = $this->customerSession->getMasqueradeAccountId();
        if ($masqueradeAccountId) {
            return $this->commMessagingHelper->getDefaultBillingAddress($masqueradeAccountId);
        } else {
            return $proceed($customerId);
        }

    }//end aroundGetDefaultBillingAddress()


    /**
     * Retrieve default shipping address for the given customerId.
     *
     * @param AccountManagement $subject    Customer AccountManagement model.
     * @param \Closure          $proceed    Parent function.
     * @param integer           $customerId Customer ID.
     *
     * @return Address|\Magento\Customer\Model\Address|mixed
     */
    public function aroundGetDefaultShippingAddress(
        AccountManagement $subject,
        \Closure $proceed,
        int $customerId
    ) {
        $masqueradeAccountId = $this->customerSession->getMasqueradeAccountId();
        if ($masqueradeAccountId) {
            return $this->commMessagingHelper->getDefaultShippingAddress($masqueradeAccountId);
        } else {
            return $proceed($customerId);
        }

    }//end aroundGetDefaultShippingAddress()


}//end class
