<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Api;

/**
 * Interface GroupsRepositoryInterface
 *
 * @package Epicor\OrderApproval\Api
 */
interface GroupsRepositoryInterface
{
    /**
     * Save groups.
     *
     * @param \Epicor\OrderApproval\Api\Data\GroupsInterface $group
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Epicor\OrderApproval\Api\Data\GroupsInterface $group);

    /**
     * Retrieve groups.
     *
     * @param int $groupId
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($groupId);

    /**
     * Delete groups.
     *
     * @param \Epicor\OrderApproval\Api\Data\GroupsInterface $group
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Epicor\OrderApproval\Api\Data\GroupsInterface $group);

    /**
     * Delete group by ID.
     *
     * @param int $groupId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($groupId);
}
