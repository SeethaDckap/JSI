<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Model\Pricing;


/**
 * Sales Rep Pricing Rule Model
 *
 * @category   Epicor
 * @package    Epicor_SalesRep
 * @author     Epicor Websales Team
 * 
 * @method setName(string $value);
 * @method setSalesRepAccountId(integer $value);
 * @method setFromDate(date $value);
 * @method setToDate(date $value);
 * @method setIsActive(integer $value);
 * @method setPriority(string $value);
 * @method setActionOperator(string $value);
 * @method setActionAmount(string $value);
 * @method setConditionsSerialized(string $value);
 * 
 * @method string getName();
 * @method integer getSalesRepAccountId();
 * @method date getFromDate();
 * @method date getToDate();
 * @method integer getIsActive();
 * @method string getPriority();
 * @method string getActionOperator();
 * @method string getActionAmount();
 * @method string getConditionsSerialized();
 * 
 */
class Rule extends \Magento\Rule\Model\AbstractModel
{

    const ENTITY = 'pricing_rule';
    const CACHE_TAG = 'pricing_rule';

    protected $_cacheTag = 'pricing_rule';
    protected $_eventPrefix = 'pricing_rule';
    protected $_eventObject = 'rule';

    /**
     * @var \Magento\Indexer\Model\Indexer
     */
    protected $indexerIndexer;

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
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Indexer\Model\Indexer $indexerIndexer,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $catalogRuleRuleConditionCombineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $catalogRuleRuleActionCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->indexerIndexer = $indexerIndexer;
        $this->catalogRuleRuleConditionCombineFactory = $catalogRuleRuleConditionCombineFactory;
        $this->catalogRuleRuleActionCollectionFactory = $catalogRuleRuleActionCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }


    function _construct()
    {
        $this->_init('Epicor\SalesRep\Model\ResourceModel\Pricing\Rule');
    }

    /**
     * Callback function which called after transaction commit in resource model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function afterCommitCallback()
    {
        parent::afterCommitCallback();

        /** @var \Magento\Indexer\Model\Indexer $indexer */
        $indexer = $this->indexerIndexer;
        /** @todo need to add new indexer type */
        //$indexer->processEntityAction($this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE);

        return $this;
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
        //M1 > M2 Translation Begin (Rule p2-1)
        //return Mage::getModel('catalogrule/rule_action_collection');
        return $this->catalogRuleRuleActionCollectionFactory->create();
        //M1 > M2 Translation End
    }

}
