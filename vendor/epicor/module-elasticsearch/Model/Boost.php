<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Epicor\Elasticsearch\Api\Data\BoostInterface;

/**
 * Boost Model
 *
 */
class Boost extends \Magento\Framework\Model\AbstractModel implements BoostInterface, IdentityInterface
{
    /**
     * @var string
     */
    const CACHE_TAG = 'ecc_search_boost_rules';

    /**
     * @var \Epicor\Elasticsearch\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    private $dateFilter;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Epicor\Elasticsearch\Model\RuleFactory $ruleFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Elasticsearch\Model\RuleFactory  $ruleFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->ruleFactory                 = $ruleFactory;
        $this->dateFilter                  = $dateFilter;
        $this->serializer                  = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave()
    {
        $this->parseDateFields();

        parent::beforeSave();
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->getData(self::BOOST_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return (string) $this->getData(self::NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function isActive()
    {
        return (bool) $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritDoc}
     */
    public function getModel()
    {
        return (string) $this->getData(self::MODEL);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig($key = null)
    {
        if (is_string($this->getData(self::CONFIG))) {
            $this->setData(self::CONFIG, $this->serializer->unserialize($this->getData(self::CONFIG)));
        }

        $result = $this->getData(self::CONFIG);

        if ($key !== null) {
            $result = isset($result[$key]) ? $result[$key] : null;
        }

        return $result;
    }

    /**
     *
     * {@inheritDoc}
     */
    public function getStoreId()
    {
        return (int) $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getFromDate()
    {
        return (string) $this->getData(self::FROM_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getToDate()
    {
        return (string) $this->getData(self::TO_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getRuleCondition()
    {
        if (!is_object($this->getData(self::RULE_CONDITION))) {
            $ruleData = $this->getData(self::RULE_CONDITION);
            $rule     = $this->ruleFactory->create();

            if (is_string($ruleData)) {
                $ruleData = $this->serializer->unserialize($ruleData);
            }

            if (is_array($ruleData)) {
                $rule->getConditions()->loadArray($ruleData);
            }
            $this->setData(self::RULE_CONDITION, $rule);
        }

        return $this->getData(self::RULE_CONDITION);
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     *
     * {@inheritDoc}
     */
    public function setId($id)
    {
        return $this->setData(self::BOOST_ID, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, (string) $name);
    }

    /**
     * {@inheritDoc}
     */
    public function setIsActive($status)
    {
        return $this->setData(self::IS_ACTIVE, (bool) $status);
    }

    /**
     * {@inheritDoc}
     */
    public function setModel($model)
    {
        return $this->setData(self::MODEL, (string) $model);
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig($config)
    {
        return $this->setData(self::CONFIG, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, (int) $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function setFromDate($fromDate)
    {
        return $this->setData(self::FROM_DATE, (string) $fromDate);
    }

    /**
     * {@inheritDoc}
     */
    public function setToDate($toDate)
    {
        return $this->setData(self::TO_DATE, (string) $toDate);
    }

    /**
     * {@inheritDoc}
     */
    public function setRuleCondition($ruleCondition)
    {
        return $this->setData(self::RULE_CONDITION, $ruleCondition);
    }

    /**
     * Validate boost data
     *
     * @param \Magento\Framework\DataObject $dataObject
     * @return bool|string[]
     * @throws \Exception
     */
    public function validateData(\Magento\Framework\DataObject $dataObject)
    {
        $result = [];
        $fromDate = $toDate = null;

        if ($dataObject->hasFromDate() && $dataObject->hasToDate()) {
            $fromDate = $dataObject->getFromDate();
            $toDate = $dataObject->getToDate();
        }

        if ($fromDate && $toDate) {
            $fromDate = $this->dateFilter->filter($fromDate);
            $toDate = $this->dateFilter->filter($toDate);

            if ($fromDate > $toDate) {
                $result[] = __('End Date must follow Start Date.');
            }
        }

        return !empty($result) ? $result : true;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init('Epicor\Elasticsearch\Model\ResourceModel\Boost');
    }

    /**
     * Parse date related fields
     *
     * @throws \Exception
     * @return void
     */
    private function parseDateFields()
    {
        if ('' === $this->getFromDate()) {
            $this->unsFromDate();
        }

        if ('' === $this->getToDate()) {
            $this->unsToDate();
        }

        foreach ([self::FROM_DATE, self::TO_DATE] as $dateField) {
            if ($this->hasData($dateField) && is_string($this->getData($dateField))) {
                $this->setData($dateField, $this->dateFilter->filter($this->getData($dateField)));
            }
        }
    }
}
