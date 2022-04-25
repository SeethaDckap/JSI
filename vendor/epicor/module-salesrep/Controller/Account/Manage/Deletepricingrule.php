<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class Deletepricingrule extends \Epicor\SalesRep\Controller\Account\Manage
{

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    /**
     * @var \Epicor\SalesRep\Model\Pricing\RuleFactory
     */
    protected $salesRepPricingRuleFactory;
    
    public function __construct(
        \Epicor\SalesRep\Controller\Context $context)
    {
        $this->salesRepPricingRuleFactory = $context->getSalesRepPricingRuleFactory();
        parent::__construct($context);
    }


    public function execute()
    {
        $helper = $this->salesRepAccountManageHelper;
        /* @var $helper Epicor_SalesRep_Helper_Account_Manage */

        $salesRepAccount = $helper->getManagedSalesRepAccount();

        $dataArr = $this->getRequest()->getParams();

        if (!empty($dataArr['id'])) {

            $rule = $this->salesRepPricingRuleFactory->create()->load($dataArr['id']);
            /* @var $rule Epicor_SalesRep_Model_Pricing_Rule */

            if ($salesRepAccount->getId() == $rule->getSalesRepAccountId()) {
                if (!$rule->isObjectNew()) {
                    $rule->delete();
                    $this->messageManager->addSuccessMessage(__('Pricing Rule Deleted Successfully'));
                }
            }
        }

        $this->_redirect('*/*/pricingrules');
    }

}
