<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class Reset extends \Epicor\SalesRep\Controller\Account\Manage
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
         \Epicor\SalesRep\Controller\Context $context    
    ) {
        $this->customerSession = $context->getCustomerSession();
        parent::__construct($context);
    }
    
    public function execute()
    {
        $customerSession = $this->customerSession;
        $customerSession->setManageSalesRepAccountId(false);
        $this->_redirect('*/*/index');
    }

}
