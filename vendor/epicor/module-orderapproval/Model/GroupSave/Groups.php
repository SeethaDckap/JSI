<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\GroupSave;

use Epicor\OrderApproval\Model\GroupSave\Utilities;
use Epicor\OrderApproval\Api\GroupsRepositoryInterface;
use Epicor\OrderApproval\Model\Groups as ApprovalGroups;

class Groups
{
    const FRONTEND_SOURCE = 'customer';

    /**
     * @var \Epicor\OrderApproval\Model\GroupSave\Utilities
     */
    private $utilities;

    /**
     * @var GroupsRepositoryInterface
     */
    private $groupsRepository;

    /**
     * @var false
     */
    private $isNewGroup = null;

    /**
     * @var ApprovalGroups
     */
    private $approvalGroups;

    /** @var string */
    private $mainGroupId;

    /**
     * Groups constructor.
     * @param \Epicor\OrderApproval\Model\GroupSave\Utilities $utilities
     * @param GroupsRepositoryInterface $groupsRepository
     * @param ApprovalGroups $approvalGroups
     */
    public function __construct(
        Utilities $utilities,
        GroupsRepositoryInterface $groupsRepository,
        ApprovalGroups $approvalGroups
    ) {
        $this->utilities = $utilities;
        $this->groupsRepository = $groupsRepository;
        $this->approvalGroups = $approvalGroups;
    }

    /**
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface|ApprovalGroups
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveMainGroup()
    {
        if ($data = $this->utilities->getPostData()) {
            $isActive = 0;
            if (isset($data['is_active_group'])) {
                $isActive = 1;
            }
            $isMultiLevel = 0;
            if (isset($data['is_multi_level'])) {
                $isMultiLevel = 1;
            }
            $isBudgetActive = isset($data['is_budget_active']) ? 1 : 0;

            $priority = $data['priority'] ?? '';
            $groupName = $data['group_name'] ?? '';
            if ($this->getPostGroupId()) {
                $this->approvalGroups = $this->getGroup();
            }
            if ($this->approvalGroups instanceof ApprovalGroups) {
                $this->approvalGroups->setData('is_budget_active', $isBudgetActive);
                $this->approvalGroups->setData('is_active', $isActive);
                $this->approvalGroups->setData('is_multi_level', $isMultiLevel);
                $this->approvalGroups->setData('priority', $priority);
                $this->approvalGroups->setData('source', self::FRONTEND_SOURCE);
                $this->approvalGroups->setData('name', $groupName);
                $this->approvalGroups->setData('created_by', $this->utilities->getCreatedByEmail());
                $this->approvalGroups = $this->groupsRepository->save($this->approvalGroups);
                $this->mainGroupId = $this->approvalGroups->getGroupId();

                return $this->approvalGroups;
            }
        }
    }

    /**
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGroup()
    {
        if ($id = $this->getPostGroupId()) {
            return $this->groupsRepository->getById($id);
        }
        return false;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isNewGroup()
    {
        if (is_null($this->isNewGroup)) {
            $this->isNewGroup = $this->getGroup() ? false : true;
        }

        return $this->isNewGroup;
    }

    /**
     * @return string
     */
    public function getPostGroupId()
    {
        $data = $this->utilities->getPostData();
        return $data['group_id_val'] ?? '';
    }

    /**
     * @return mixed
     */
    public function getMainGroupId()
    {
        return $this->mainGroupId;
    }

    /**
     * @return ApprovalGroups
     */
    public function getMainGroup()
    {
        return $this->approvalGroups;
    }
}
