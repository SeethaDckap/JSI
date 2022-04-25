<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Api\Data;

/**
 * Interface BoostInterface
 *
 * @package Epicor\Elasticsearch\Api
 * @api
 */
interface BoostInterface
{
    /**
     * Table that holds the boost related information
     */
    const TABLE_NAME = 'ecc_search_boost_rules';

    /**
     * Constant for field boost_id
     */
    const BOOST_ID = 'boost_id';

    /**
     * Constant for field name
     */
    const NAME = 'name';

    /**
     * Constant for field is_active
     */
    const IS_ACTIVE = 'is_active';

    /**
     * Constant for field model
     */
    const MODEL = 'model';

    /**
     * Constant for field config
     */
    const CONFIG = 'config';

    /**
     * Constant for field store_id
     */
    const STORE_ID = 'store_id';

    /**
     * Constant for field from_date
     */
    const FROM_DATE = 'from_date';

    /**
     * Constant for field to_date
     */
    const TO_DATE = 'to_date';

    /**
     * Constant for field rule_condition
     */
    const RULE_CONDITION = 'rule_condition';

    /**
     * Get Boost ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Get boost status
     *
     * @return bool
     */
    public function isActive();

    /**
     * Get model
     *
     * @return string
     */
    public function getModel();

    /**
     * Get config
     *
     * @return string
     */
    public function getConfig();

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Get from_date
     *
     * @return string
     */
    public function getFromDate();

    /**
     * Get to_date
     *
     * @return string
     */
    public function getToDate();

    /**
     * Get rule_condition
     *
     * @return \Epicor\Elasticsearch\Api\Data\ConditionInterface
     */
    public function getRuleCondition();

    /**
     * Set id
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     *
     * @param int $id Boost id.
     *
     * @return BoostInterface
     */
    public function setId($id);

    /**
     * Set name
     *
     * @param string $name
     *
     * @return BoostInterface
     */
    public function setName($name);

    /**
     * Set boost status
     *
     * @param bool $status
     *
     * @return BoostInterface
     */
    public function setIsActive($status);

    /**
     * Set model
     *
     * @param string $model
     *
     * @return BoostInterface
     */
    public function setModel($model);

    /**
     * Set config
     *
     * @param string $config
     *
     * @return BoostInterface
     */
    public function setConfig($config);

    /**
     * Set store id
     *
     * @param int $storeId
     *
     * @return BoostInterface
     */
    public function setStoreId($storeId);

    /**
     * Set from_date
     *
     * @param string|null $fromDate
     *
     * @return BoostInterface
     */
    public function setFromDate($fromDate);

    /**
     * Set to_date
     *
     * @param string|null $toDate
     *
     * @return BoostInterface
     */
    public function setToDate($toDate);

    /**
     * Set rule_condition
     *
     * @param string $ruleCondition
     *
     * @return string
     */
    public function setRuleCondition($ruleCondition);
}
