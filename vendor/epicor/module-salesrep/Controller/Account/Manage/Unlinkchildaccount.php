<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class Unlinkchildaccount extends \Epicor\SalesRep\Controller\Account\Manage
{

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
         \Epicor\SalesRep\Controller\Context $context,    
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->salesRepAccountManageHelper = $context->getSalesRepAccountManageHelper();
        $this->logger = $context->getLogger();
        parent::__construct($context);
    }
    public function execute()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper Epicor_SalesRep_Helper_Account_Manage */

        $salesRepAccount = $helper->getManagedSalesRepAccount();
        
        /* @var $salesRepAccount Epicor_SalesRep_Model_Account */

        try {
            $childAccountId = unserialize(base64_decode($this->getRequest()->getParam('salesrepaccount')));
            $salesRepAccount->removeChildAccount($childAccountId);
            $salesRepAccount->save();
            //M1 > M2 Translation Begin (Rule 55)
            //$msg = __('Child Sales Rep Account has been unlinked from %s', $salesRepAccount->getName());
            $msg = __('Child Sales Rep Account has been unlinked from %1', $salesRepAccount->getName());
            //M1 > M2 Translation End
        } catch (Exception $ex) {
            $this->logger->critical($ex);
            $error = __('An error occured, please try again');
        }

        if (!empty($error)) {
            $this->messageManager->addErrorMessage($error);
        } else {
            $this->messageManager->addSuccessMessage($msg);
        }

        $this->_redirect('*/*/hierarchy');
    }
}
