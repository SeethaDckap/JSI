<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Api\Data;

/**
 * OrderApproval Groups interface.
 * @api
 */
interface GroupsInterface
{
    const SOURCE_ADMIN = 'web';

    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const GROUP_ID         = 'group_id';
    const NAME             = 'name';
    const IS_ACTIVE        = 'is_active';
    const IS_MULTI_LEVEL   = 'is_multi_level';
    const RULES            = 'rules';
    const SOURCE           = 'source';
    const PRIORITY         = 'priority';
    const IS_BUDGET_ACTIVE = 'is_budget_active';
    const CREATED_AT       = 'created_at';
    const UPDATED_AT       = 'updated_at';

    /**
     * Get Group Id
     *
     * @return int|null
     */
    public function getGroupId();

    /**
     * Get Name
     *
     * @return string
     */
    public function getName();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getIsActive();

    /**
     * get multi level
     *
     * @return mixed
     */
    public function getIsMultiLevel();

    /**
     * Get Rules
     *
     * @return string|null
     * @since 101.0.0
     */
    public function getRules();

    /**
     * Get Source
     *
     * @return string|null
     */
    public function getSource();

    /**
     * Get Source
     *
     * @return int|null
     */
    public function getPriority();

    /**
     * Get Budget Is Active.
     *
     * @return boolean
     */
    public function getIsBudgetActive();

    /**
     * Get Meta Description
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set Group Id
     *
     * @param int $groupId
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setGroupId($groupId);

    /**
     * Set Name
     *
     * @param string $name
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setName($name);

    /**
     * Set Is Active
     *
     * @param string $isActive
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setIsActive($isActive);

    /**
     * Set Is Multi Level
     *
     * @param string $isMultiLevel
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     * @since 101.0.0
     */
    public function setIsMultiLevel($isMultiLevel);

    /**
     * Set Rules
     *
     * @param string $rules
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setRules($rules);

    /**
     * Set Source
     *
     * @param string $source
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setSource($source);

    /**
     * Set priority
     *
     * @param int $priority
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setPriority($priority);

    /**
     * Set Is Budget Active
     *
     * @param boolean $isBudgetActive
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setIsBudgetActive($isBudgetActive);

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     */
    public function setUpdatedAt($updatedAt);


}
