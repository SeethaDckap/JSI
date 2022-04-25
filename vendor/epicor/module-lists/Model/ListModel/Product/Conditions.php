<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Product;


/**
 * List Product Conditions Model
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 *
 */
class Conditions extends \Magento\Rule\Model\AbstractModel
{

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    protected $catalogRuleRuleConditionCombineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    protected $catalogRuleRuleActionCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ResourceModel\List\Product\ConditionsFactory
     */
    protected $listsResourceListProductConditionsFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $catalogRuleRuleConditionCombineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $catalogRuleRuleActionCollectionFactory,
        \Epicor\Lists\Model\ResourceModel\ListModel\Product\ConditionsFactory $listsResourceListProductConditionsFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        $this->catalogRuleRuleConditionCombineFactory = $catalogRuleRuleConditionCombineFactory;
        $this->catalogRuleRuleActionCollectionFactory = $catalogRuleRuleActionCollectionFactory;
        $this->listsResourceListProductConditionsFactory = $listsResourceListProductConditionsFactory;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }


    /**
     * Getter for rule conditions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
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
        return $this->catalogRuleRuleActionCollectionFactory->create();
    }

    /**
     * Build Condition SQL
     */
    public function buildSql()
    {
        $resource = $this->listsResourceListProductConditionsFactory->create();
        /* @var $resource \Epicor\Lists\Model\ResourceModel\ListModel\Product\Conditions */

        return $resource->buildSql($this);
    }

}
