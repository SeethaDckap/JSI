<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Model\Condition\Sql;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Combine;
use Magento\Eav\Api\AttributeRepositoryInterface;

/**
 * Class SQL Builder
 *
 * @package Magento\Rule\Model\Condition\Sql
 */
class Builder extends \Magento\Rule\Model\Condition\Sql\Builder
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var array
     */
    protected $_conditionOperatorMap = [
        '=='    => ':field = ?',
        '!='    => ':field <> ?',
        '>='    => ':field >= ?',
        '>'     => ':field > ?',
        '<='    => ':field <= ?',
        '<'     => ':field < ?',
        '{}'    => ':field LIKE ("%"?"%")',
        '!{}'   => ':field NOT LIKE ("%"?"%")',
        '()'    => ':field IN (?)',
        '!()'   => ':field NOT IN (?)',
    ];

    /**
     * @var \Magento\Rule\Model\Condition\Sql\ExpressionFactory
     */
    protected $_expressionFactory;
    
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $productMetadata;
    
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param ExpressionFactory $expressionFactory
     * @param AttributeRepositoryInterface|null $attributeRepository
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Sql\ExpressionFactory $expressionFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        AttributeRepositoryInterface $attributeRepository = null
    )
    {
        $this->_expressionFactory = $expressionFactory;
        $this->productMetadata = $productMetadata;
        $this->attributeRepository = $attributeRepository ?:
            ObjectManager::getInstance()->get(AttributeRepositoryInterface::class);
        if($this->productMetadata->getVersion() < '2.2.5'){
            parent::__construct($expressionFactory);
        } else {
            parent::__construct($expressionFactory, $attributeRepository);
        }
    }

    /**
     * @param AbstractCondition $condition
     * @param string $value
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getMappedSqlConditionecc(AbstractCondition $condition, $value = '', $isDefaultStoreUsed = true)
    {
        $argument = $condition->getMappedSqlField();
        if ($argument) {
            $conditionOperator = $condition->getOperatorForValidate();

            if (!isset($this->_conditionOperatorMap[$conditionOperator])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unknown condition operator'));
            }

            if ($condition->getAttributeObject()->getAttributeCode() == 'category_ids' && $conditionOperator == '{}') {
                $finalOperator = ':field IN (?)';
            } elseif ($condition->getAttributeObject()->getAttributeCode() == 'category_ids' && $conditionOperator == '{}') {
                $finalOperator = ':field NOT IN (?)';
            } else {
                $finalOperator = $this->_conditionOperatorMap[$conditionOperator];
            }
            
            $defaultValue = 0;
            // Check if attribute has a table with default value and add it to the query
            if ($this->canAttributeHaveDefaultValue($condition->getAttribute(), $isDefaultStoreUsed)) {
                $defaultField = 'at_' . $condition->getAttribute() . '_default.value';
                $defaultValue = $this->_connection->quoteIdentifier($defaultField);
            }
            
            $sql = str_replace(
                    ':field', $this->_connection->getIfNullSql($this->_connection->quoteIdentifier($argument), $defaultValue), 
                    $finalOperator
            );

            return $this->_expressionFactory->create(
                ['expression' => $value . $this->_connection->quoteInto($sql, $condition->getBindArgumentValue())]
            );
        }
        return '';
    }

    /**
     * @param Combine $combine
     * @param string $value
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getMappedSqlCombinationecc(Combine $combine, $value = '', $isDefaultStoreUsed = true)
    {
        $out = (!empty($value) ? $value : '');
        $value = ($combine->getValue() ? '' : ' NOT ');
        $getAggregator = $combine->getAggregator();
        $conditions = $combine->getConditions();
        foreach ($conditions as $key => $condition) {
            /** @var $condition AbstractCondition|Combine */
            $con = ($getAggregator == 'any' ? Select::SQL_OR : Select::SQL_AND);
            $con = (isset($conditions[$key+1]) ? $con : '');
            if ($condition instanceof Combine) {
                $out .= $this->_getMappedSqlCombinationecc($condition, $value, $isDefaultStoreUsed);
            } else {
                $out .= $this->_getMappedSqlConditionecc($condition, $value, $isDefaultStoreUsed);
            }
            $out .=  $out ? (' ' . $con) : '';
        }
        return $this->_expressionFactory->create(['expression' => $out]);
    }

    /**
     * Attach conditions filter to collection
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param Combine $combine
     *
     * @return void
     */
    
    public function attachConditionToCollectionList(
            $collection,
        Combine $combine
    ) {
        $this->_connection = $collection->getResource()->getConnection();
        $whereExpression = (string)$this->_getMappedSqlCombinationecc($combine);
        if (!empty($whereExpression)) {
            return  $whereExpression;
            // Select ::where method adds braces even on empty expression
          //  $collection->getSelect()->where($whereExpression);
        }
    }
    
    /**
     * Adding this method as this won't be available in Magento version < 2.2.5
     * Check if attribute can have default value
     *
     * @param string $attributeCode
     * @param bool $isDefaultStoreUsed
     * @return bool
     */
    private function canAttributeHaveDefaultValue(string $attributeCode, bool $isDefaultStoreUsed): bool
    {
        if ($isDefaultStoreUsed) {
            return false;
        }

        try {
            $attribute = $this->attributeRepository->get(Product::ENTITY, $attributeCode);
        } catch (NoSuchEntityException $e) {
            // It's not exceptional case as we want to check if we have such attribute or not
            return false;
        }

        return !$attribute->isScopeGlobal();
    }
}
