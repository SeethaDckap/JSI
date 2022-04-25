<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model\ResourceModel;

use Epicor\Elasticsearch\Api\Data\BoostInterface;
use Epicor\Elasticsearch\Model\RuleFactory;

/**
 * Boost Resource Model
 */
class Boost extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Framework\App\Cache\Type\FrontendPool
     */
    private $frontendCachePool;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param RuleFactory $ruleFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $frontendCachePool
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        RuleFactory $ruleFactory,
	\Magento\Framework\Serialize\SerializerInterface $serializer,
	\Magento\Framework\App\Cache\Type\FrontendPool $frontendCachePool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->ruleFactory = $ruleFactory;
	$this->serializer  = $serializer;
	$this->frontendCachePool = $frontendCachePool;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(BoostInterface::TABLE_NAME, BoostInterface::BOOST_ID);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
	parent::_afterSave($object);
        $this->frontendCachePool->get(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER)->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            ['ecc_search_boost_rules']
        );
        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterDelete($object);
        $this->frontendCachePool->get(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER)->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            ['ecc_search_boost_rules']
        );
        return $this;
    }    

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->getConfig();
        $object->getRuleCondition();
        return parent::_afterLoad($object);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (is_array($object->getConfig())) {
            $object->setConfig($this->serializer->serialize($object->getConfig()));
        }
        $rule = $this->ruleFactory->create();
        $ruleCondition = $object->getRuleCondition();
        if (is_object($ruleCondition)) {
            $rule = $ruleCondition;
        } elseif (is_array($ruleCondition)) {
            $rule->getConditions()->loadArray($ruleCondition);
        }
        $object->setRuleCondition($this->serializer->serialize($rule->getConditions()->asArray()));
        return parent::_beforeSave($object);
    }
}
