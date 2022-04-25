<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep;

class Deletepricingrule extends \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep
{

    /**
     * @var \Epicor\SalesRep\Model\Pricing\RuleFactory
     */
    protected $salesRepPricingRuleFactory;

    public function __construct(
        \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Context $context)
    {
        $this->salesRepPricingRuleFactory = $context->getSalesRepPricingRuleFactory();

        parent::__construct($context);
    }


    public function execute()
    {
        $dataArr = $this->getRequest()->getParams();

        if (!empty($dataArr['id'])) {
            $rule = $this->salesRepPricingRuleFactory->create()->load($dataArr['id']);
            /* @var $rule \Epicor\SalesRep\Model\Pricing\Rule */
            if (!$rule->isObjectNew()) {
                $rule->delete();
            }
        }
        $this->_redirect('*/*/edit');
    }

}
