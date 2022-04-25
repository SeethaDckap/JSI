<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Api;

use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Interface ErpAccountRepositoryInterface
 *
 * @package Epicor\OrderApproval\Api
 */
interface HierarchyRepositoryInterface
{
    /**
     * @param Data\LinkInterface $hierarchy
     *
     * @return mixed
     */
    public function save(Data\LinkInterface $hierarchy);

    /**
     * @param $groupId
     *
     * @return int
     */
    public function deleteByGroupId($groupId);

    /**
     * @param $parentGroupId
     *
     * @return int
     */
    public function deleteByParentGroupId($parentGroupId);

    /**
     * @param Data\LinkInterface $hierarchy
     *
     * @return mixed
     */
    public function delete(Data\LinkInterface $hierarchy);

}
