<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Cart;

class Removeminicart extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $salesRepHelper = $this->salesRepHelper;
        /* @var $salesRepHelper Epicor_SalesRep_Helper_Data */

        if (!$salesRepHelper->isEnabled()) {
            return;
        }

        $customerSession = $this->customerSession;
        /* @var $customerSession \Magento\Customer\Model\Session */

        $customer = $customerSession->getCustomer();

        /* @var $customer Epicor_Comm_Model_Customer */
        if ($customer->isSalesRep() && !$salesRepHelper->isMasquerading()) {
           $layout = $observer->getLayout();
           $layout->unsetElement('epicor_comm.cart.quickadd');
           $layout->unsetElement('epicor_comm.locationpicker');
           $layout->unsetElement('right.customer.account.summary');
           $layout->unsetElement('minicart');
        }
    }

}
