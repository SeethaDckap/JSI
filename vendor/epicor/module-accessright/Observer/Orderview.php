<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\AccessRight\Observer;

use Magento\Framework\App\RequestInterface;
class Orderview implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * @var \\Epicor\AccessRight\Helper\Data
     */
    protected $_accesshelper;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    public function __construct(
        \Epicor\AccessRight\Helper\Data $authorization,
        RequestInterface $requestInterface
    )
    {
        $this->_accesshelper = $authorization;
        $this->_accessauthorization = $authorization->getAccessAuthorization();
        $this->requestInterface = $requestInterface;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $actions = $this->_accesshelper->coreActionList();
        /* @var $customer \Epicor\Comm\Model\Customer */
        if (isset($actions[$observer->getEvent()->getFullActionName()])) {
            if($observer->getEvent()->getFullActionName() == "sales_order_view" && $this->getRequest()->getParam('view_order_approval')) {
                $actions[$observer->getEvent()->getFullActionName()] = "Epicor_Customer::my_account_approvals_details";
            }
            if (!$this->_accessauthorization->isAllowed($actions[$observer->getEvent()->getFullActionName()])) {
                $observer->getEvent()->getLayout()->getUpdate()->removeHandle($observer->getEvent()->getFullActionName());
                $observer->getEvent()->getLayout()->getUpdate()->addHandle('frontend_denied_account');
            }
        }
    }

    /**
     * Get request
     *
     * @return \Magento\Framework\App\RequestInterface
     */
    public function getRequest()
    {
        return $this->requestInterface;
    }

}