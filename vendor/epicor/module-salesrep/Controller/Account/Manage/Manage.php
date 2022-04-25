<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class Manage extends \Epicor\SalesRep\Controller\Account\Manage
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
       \Epicor\SalesRep\Controller\Context $context
    ) {
        $this->registry = $context->getRegistry();
        $this->customerSession = $context->getCustomerSession();
         parent::__construct($context);
    }
    public function execute()
    {
        $encodedId = $this->getRequest()->getParam('salesrepaccount');
        $salesRepAccountId = unserialize(base64_decode($encodedId));

        $baseAccount = $this->registry->registry('sales_rep_account_base');

        if ($baseAccount->hasChildAccount($salesRepAccountId)) {
            $customerSession = $this->customerSession;
            $customerSession->setManageSalesRepAccountId($salesRepAccountId);
        }

        $this->_redirect('*/*/index');
    }

    }
