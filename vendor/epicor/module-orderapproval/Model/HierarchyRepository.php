<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Epicor\OrderApproval\Api\HierarchyRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Link as LinkHierarchy;
use \Epicor\OrderApproval\Api\Data\LinkInterface;
use Epicor\OrderApproval\Model\Groups\LinkFactory;

/**
 * Class HierarchyRepository
 *
 * @package Epicor\OrderApproval\Model
 */
class HierarchyRepository implements HierarchyRepositoryInterface
{
    /**
     * @var LinkHierarchy
     */
    private $resource;

    /**
     * HierarchyRepository constructor.
     *
     * @param LinkHierarchy $resource
     */
    public function __construct(
        LinkHierarchy $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param LinkInterface $hierarchy
     *
     * @return LinkInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save(LinkInterface $hierarchy)
    {
        try {
            $this->resource->save($hierarchy);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the erp account: %1',
                    $exception->getMessage()),
                $exception
            );
        }

        return $hierarchy;
    }

    /**
     * @param string $groupId
     *
     * @return int
     * @throws CouldNotDeleteException
     */
    public function deleteByGroupId($groupId)
    {
        try {
            $count = $this->resource->getConnection()
                ->delete('ecc_approval_group_link',
                    ['group_id = ?' => $groupId]);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Group: %1', $exception->getMessage())
            );
        }

        return $count;
    }

    /**
     * @param $parentGroupId
     *
     * @return int
     * @throws CouldNotDeleteException
     */
    public function deleteByParentGroupId($parentGroupId)
    {
        try {
            $count = $this->resource->getConnection()
                ->delete('ecc_approval_group_link',
                    ['parent_group_id = ?' => $parentGroupId]);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Group: %1', $exception->getMessage())
            );
        }

        return $count;
    }

    /**
     * @param LinkInterface $hierarchy
     *
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function delete(LinkInterface $hierarchy)
    {
        try {
            $this->resource->delete($hierarchy);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Group: %1', $exception->getMessage())
            );
        }

        return true;
    }
}
