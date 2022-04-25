<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Observer\Access;

class CanAccessUrlAfter extends AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->salesRepHelper;
        /* @var $helper \Epicor\SalesRep\Helper\Data */

        $url = $observer->getEvent()->getUrl();
        $urlInfo = $observer->getEvent()->getUrlModuleInfo();
        $transport = $observer->getEvent()->getTransport();
        /* @var $transport Varien_Object */

        if (empty($url)) {
            return;
        }

        $customerSession = $this->customerSession;
        /* @var $customerSession \Magento\Customer\Model\Session */

        $customer = $customerSession->getCustomer();
        /* @var $customer \Epicor\Comm\Model\Customer */

        $allowed = $transport->getAllowed();

        if ($customer->isSalesRep()) {
            if (!$helper->isLicensedFor(array('Consumer', 'Customer'))) {
                $allowed = false;
            } else {
                $srAllowed = $this->_isUrlAllowed($allowed, $url, $helper->isMasquerading());
                if (!$srAllowed) {
                    $allowed = false;
                }
            }

            //Mage_Catalog wont work in M2. Also all the category navigations are coming through controller and loaded via Ajax KO
            if ($allowed && !$helper->isMasquerading() && $urlInfo['action'] == 'view' && $urlInfo['controller'] == 'Category') {
                $allowed = $helper->salesRepHasCatalogAccess($customer);
            }   
        } else {
            if (strpos($url, 'salesrep') !== false) {
                $allowed = false;
            }
        }

        $transport->setAllowed($allowed);
    }

}