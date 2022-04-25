<?php

/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 *
 * @package    Epicor_Punchout
 * @subpackage Observer
 * @author     Epicor Websales Team
 * @copyright  Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Observer\Msq;

use Epicor\Comm\Helper\Messaging;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;

/**
 * Class AlterMethodCode
 */
class UpdateData implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Shipping model.
     *
     * @var Messaging
     */
    private $messageHelper;

    /**
     * Customer Model.
     *
     * @var Customer
     */
    private $customerModel;


    /**
     * Constructor.
     *
     * @param Messaging $messageHelper
     * @param Customer $customerModel
     */
    public function __construct(
        Messaging $messageHelper,
        Customer $customerModel
    ) {
        $this->messageHelper = $messageHelper;
        $this->customerModel = $customerModel;

    }//end __construct()


    /**
     * Execute function.
     *
     * @param Observer $observer Event observer.
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote       = $observer->getEvent()->getQuote();
        $msq         = $observer->getEvent()->getMessage();
        if ($quote && $quote->getIsPunchout()) {

            $erpAccountId = $quote->getEccErpAccountId();
            $erpAccount   = $this->messageHelper->getErpAccountInfo($erpAccountId);

            if ($erpAccount->getId()) {
                $msq->setCustomerGroupId($erpAccountId);
                $msq->setIsPunchout(1);

                $customer = $this->customerModel->load($quote->getCustomerId());
                $msq->setCustomerObj($customer);
            }
        }

    }//end execute()


}//end class
