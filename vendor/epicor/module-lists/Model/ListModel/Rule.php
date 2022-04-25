<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel;


class Rule extends \Magento\Rule\Model\AbstractModel
{

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $catalogRuleRuleConditionCombineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    protected $catalogRuleRuleActionCollectionFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $catalogRuleRuleConditionCombineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $catalogRuleRuleActionCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->catalogRuleRuleConditionCombineFactory = $catalogRuleRuleConditionCombineFactory;
        $this->catalogRuleRuleActionCollectionFactory = $catalogRuleRuleActionCollectionFactory;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }


    public function _construct()
    {
        parent::_construct();
        $this->_init('Epicor\Lists\Model\ResourceModel\ListModel');
    }

    public function getConditionsInstance()
    {
        return $this->catalogRuleRuleConditionCombineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        //M1 > M2 Translation Begin (Rule p2-1)
        //return Mage::getModel('catalogrule/rule_action_collection');
        return $this->catalogRuleRuleActionCollectionFactory->create();
        //M1 > M2 Translation End
    }

}
