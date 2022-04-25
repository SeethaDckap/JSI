<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Epicor\OrderApproval\Api\GroupsRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Epicor\OrderApproval\Model\ResourceModel\Groups as ResourceGroup;
use Epicor\OrderApproval\Model\ResourceModel\GroupsFactory as GroupsResourceFactory;
use Epicor\OrderApproval\Model\GroupsFactory;

/**
 * Class GroupRepository
 *
 * @package Epicor\OrderApproval\Model
 */
class GroupsRepository implements GroupsRepositoryInterface
{
    /**
     * @var ResourceGroup
     */
    protected $resource;

    /**
     * @var GroupsFactory
     */
    protected $groupFactory;

    /**
     * @var GroupsResourceFactory
     */
    protected $groupResourceFactory;

    /**
     * GroupsRepository constructor.
     *
     * @param ResourceGroup         $resource
     * @param GroupsFactory         $groupFactory
     * @param GroupsResourceFactory $groupResourceFactory
     */
    public function __construct(
        ResourceGroup $resource,
        GroupsFactory $groupModelFactory,
        GroupsResourceFactory $groupResourceFactory
    ) {
        $this->resource = $resource;
        $this->groupFactory = $groupModelFactory;
        $this->groupResourceFactory = $groupResourceFactory;
    }


    /**
     * Save groups data
     *
     * @param \Epicor\OrderApproval\Api\Data\GroupsInterface|Groups $group
     *
     * @return Groups
     * @throws CouldNotSaveException
     */
    public function save(\Epicor\OrderApproval\Api\Data\GroupsInterface $group)
    {
        try {
            $this->resource->save($group);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the groups: %1', $exception->getMessage()),
                $exception
            );
        }

        return $group;
    }

    /**
     * @param int $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface|Groups
     * @throws NoSuchEntityException
     */
    public function getById($groupId)
    {
        /** @var Groups $group */
        $group = $this->groupFactory->create();
        if($groupId) {
            $this->groupResourceFactory->create()->load($group, $groupId);
        }

        if ( ! $group->getId()) {
            throw new NoSuchEntityException(
                __('The group with the "%1" groupId doesn\'t exist.', $groupId)
            );
        }

        return $group;
    }

    /**
     * Delete groups
     *
     * @param \Epicor\OrderApproval\Api\Data\GroupsInterface $group
     *
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Epicor\OrderApproval\Api\Data\GroupsInterface $group
    ) {
        try {
            $this->resource->delete($group);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Group: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * Delete groups by given groups Identity
     *
     * @param string $groupId
     *
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($groupId)
    {
        return $this->delete($this->getById($groupId));
    }
}
