<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Account\Manage;

class Pricingrulespost extends \Epicor\SalesRep\Controller\Account\Manage
{

    /**
     * @var \Epicor\SalesRep\Model\Pricing\RuleFactory
     */
    protected $salesRepPricingRuleFactory;

    /**
     * @var \Epicor\SalesRep\Helper\Account\Manage
     */
    protected $salesRepAccountManageHelper;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $dataObject;

    public function __construct(
       \Epicor\SalesRep\Controller\Context $context,     
        \Magento\Framework\DataObject $dataObject
    ) {
        $this->dataObject= $dataObject;
        $this->salesRepPricingRuleFactory = $context->getSalesRepPricingRuleFactory();
        $this->salesRepAccountManageHelper =  $context->getSalesRepAccountManageHelper();
         parent::__construct($context);
    }
    public function execute()
    {
        $dataArr = $this->getRequest()->getParams();

        if (isset($dataArr['rule'])) {
            $dataArr['conditions'] = $dataArr['rule']['conditions'];
            unset($dataArr['rule']);
        }
        $data = $this->dataObject->addData($dataArr);
         if ($data->getName()) {
                $rule = $this->salesRepPricingRuleFactory->create()->load($data->getId());
                /* @var $rule Epicor_SalesRep_Model_Pricing_Rule */
                unset($dataArr['id']);
                $rule->loadPost($dataArr);

                $helper = $this->salesRepAccountManageHelper;
                /* @var $helper Epicor_SalesRep_Helper_Account_Manage */
                $salesRepAccount = $helper->getManagedSalesRepAccount();

                $rule->setName($data->getName());
                $rule->setSalesRepAccountId($salesRepAccount->getId());
                $rule->setFromDate($data->getFromDate());
                $rule->setToDate($data->getToDate());
                $rule->setIsActive($data->getIsActive());
                $rule->setPriority($data->getPriority());
                $rule->setActionOperator($data->getActionOperator());
                $rule->setActionAmount($data->getActionAmount());

                $rule->save();
         }
    }

}
