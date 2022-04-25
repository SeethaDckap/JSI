<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Customer;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
class CheckLogin extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Epicor\SalesRep\Model\AccountFactory
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->salesRepHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Data */

        $customer = $observer->getEvent()->getModel();
        /* @var $customer \Epicor\Comm\Model\Customer */
        if ($customer->isSalesRep() && !$helper->isEnabled()) {
            //M1 > M2 Translation Begin (Rule P2-9)
            //throw Mage::exception('Mage_Core', $this->__('Invalid login or password (007)'), \Magento\Customer\Model\Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD);
            throw new InvalidEmailOrPasswordException(__('Invalid login or password (007)'));
            //M1 > M2 Translation End

        }
    }

}