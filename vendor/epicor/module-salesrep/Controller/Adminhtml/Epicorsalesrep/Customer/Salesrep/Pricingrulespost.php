<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep;

class Pricingrulespost extends \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Salesrep
{


    /**
     * @var \Epicor\SalesRep\Model\Pricing\RuleFactory
     */
    protected $salesRepPricingRuleFactory;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $dataObject;

    /**
     * @var \Epicor\SalesRep\Model\Pricing\Rule\Product\Indexer\ProductProcessor
     */
    protected $ruleProcessor;

    public function __construct(
        \Magento\Framework\DataObject $dataObject,
        \Epicor\SalesRep\Model\Pricing\Rule\Product\Indexer\ProductProcessor $ruleProcessor,
        \Epicor\SalesRep\Controller\Adminhtml\Epicorsalesrep\Customer\Context $context
    )
    {
        $this->dataObject = $dataObject;
        $this->ruleProcessor = $ruleProcessor;
        $this->salesRepPricingRuleFactory = $context->getSalesRepPricingRuleFactory();

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
            /* @var $rule \Epicor\SalesRep\Model\Pricing\Rule */

            unset($dataArr['id']);

            $rule->loadPost($dataArr);

            $rule->setName($data->getName());
            $rule->setSalesRepAccountId($data->getSalesrepAccountId());
            $rule->setFromDate($data->getFromDate());
            $rule->setToDate($data->getToDate());
            $rule->setIsActive($data->getIsActive());
            $rule->setPriority($data->getPriority());
            $rule->setActionOperator($data->getActionOperator());
            $rule->setActionAmount($data->getActionAmount());

            $rule->save();            
            $this->ruleProcessor->reindexRow($rule->getId());
        }
    }

    }
