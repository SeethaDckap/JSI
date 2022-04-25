<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model;

use Epicor\OrderApproval\Model\ResourceModel\Groups\CollectionFactory as GroupCollFactory;
use Epicor\OrderApproval\Model\ResourceModel\Groups\Collection as GroupCollection;
use Epicor\OrderApproval\Model\HierarchyRepositoryFactory as HierarchyRepositoryFactory;
use Epicor\OrderApproval\Api\Data\LinkInterfaceFactory as LinkInterfaceFactory;
use Magento\Backend\Helper\Js as BackendJsHelper;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Model Class for OrderApproval
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Ecc Team
 *
 */
class HierarchyManagement
{
    /**
     * @var int
     */
    private $groupId = 0;

    /**
     * @var GroupCollFactory
     */
    private $groupCollFactory;

    /**
     * @var \Epicor\OrderApproval\Model\HierarchyRepositoryFactory
     */
    private $hierarchyRepositoryFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var LinkInterfaceFactory
     */
    private $linkInterfaceFactory;

    /**
     * @var BackendJsHelper
     */
    private $backendJsHelper;

    /**
     * HierarchyManagement constructor.
     *
     * @param GroupCollFactory                                       $groupCollFactory
     * @param \Epicor\OrderApproval\Model\HierarchyRepositoryFactory $hierarchyRepositoryFactory
     * @param DataObjectHelper                                       $dataObjectHelper
     * @param LinkInterfaceFactory                                   $linkInterfaceFactory
     * @param BackendJsHelper                                        $backendJsHelper
     */
    public function __construct(
        GroupCollFactory $groupCollFactory,
        HierarchyRepositoryFactory $hierarchyRepositoryFactory,
        DataObjectHelper $dataObjectHelper,
        LinkInterfaceFactory $linkInterfaceFactory,
        BackendJsHelper $backendJsHelper
    ) {
        $this->groupCollFactory = $groupCollFactory;
        $this->hierarchyRepositoryFactory = $hierarchyRepositoryFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->linkInterfaceFactory = $linkInterfaceFactory;
        $this->backendJsHelper = $backendJsHelper;
    }

    /**
     * @param null|string $groupId
     *
     * @return GroupCollection
     */
    public function getParentCollection($groupId = null)
    {
        if ($groupId) {
            $this->groupId = $groupId;
        }

        /* @var $collection GroupCollection */
        $collection = $this->groupCollFactory->create();
        $collection->getSelect()->join(
            array(
                'link' => $collection->getTable(
                    'ecc_approval_group_link'
                ),
            ),
            'main_table.group_id = link.group_id AND link.group_id = "'
            .$this->getGroupId().'"', array('parent_group_id')
        );

        return $collection;
    }

    /**
     * @param null|string $groupId
     *
     * @return GroupCollection
     */
    public function getCollectionByParentId($groupId = null)
    {
        if ($groupId) {
            $this->groupId = $groupId;
        }

        /* @var $collection GroupCollection */
        $collection = $this->groupCollFactory->create();
        $collection->getSelect()->join(
            array(
                'link' => $collection->getTable(
                    'ecc_approval_group_link'
                ),
            ),
            'main_table.group_id = link.parent_group_id AND link.group_id = "'
            .$this->getGroupId().'"', array('parent_group_id')
        );

        return $collection;
    }

    /**
     * @param null $groupId
     *
     * @return GroupCollection
     */
    public function getChildrenCollection($groupId = null)
    {
        if ($groupId) {
            $this->groupId = $groupId;
        }

        $collection = $this->groupCollFactory->create();
        $collection->getSelect()->join(
            array(
                'link' => $collection->getTable(
                    'ecc_approval_group_link'
                ),
            ),
            'main_table.group_id = link.group_id AND link.parent_group_id = "'
            .$this->getGroupId().'"', array('parent_group_id')
        );

        return $collection;
    }

    /**
     * @param array  $data
     * @param string $groupId
     */
    public function saveParentHierarchy($data, $groupId)
    {
        /** @var \Epicor\OrderApproval\Model\Groups\Link $link */
        $link = $this->linkInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray($link,
            [
                \Epicor\OrderApproval\Api\Data\LinkInterface::GROUP_ID        => $groupId,
                \Epicor\OrderApproval\Api\Data\LinkInterface::PARENT_GROUP_ID => $data['hierarchy']['parent'],
                \Epicor\OrderApproval\Api\Data\LinkInterface::BY_CUSTOMER     => 0,
                \Epicor\OrderApproval\Api\Data\LinkInterface::BY_GROUP        => 1,
            ],
            \Epicor\OrderApproval\Api\Data\LinkInterface::class);

        $hierarchyRepository = $this->hierarchyRepositoryFactory->create();
        $hierarchyRepository->deleteByGroupId($groupId);
        $hierarchyRepository->save($link);
    }

    /**
     * @param array  $data
     * @param string $parentGroupId
     */
    public function saveChildrenHierarchy($data, $parentGroupId)
    {
        $hierarchyRepository = $this->hierarchyRepositoryFactory->create();
        $hierarchyRepository->deleteByParentGroupId($parentGroupId);

        $groupIds
            = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['hierarchy']['children']));
        foreach ($groupIds as $key => $value) {
            /** @var \Epicor\OrderApproval\Model\Groups\Link $link */
            $link = $this->linkInterfaceFactory->create();
            $groupId = $value;
            $this->dataObjectHelper->populateWithArray($link,
                [
                    \Epicor\OrderApproval\Api\Data\LinkInterface::GROUP_ID        => $groupId,
                    \Epicor\OrderApproval\Api\Data\LinkInterface::PARENT_GROUP_ID => $parentGroupId,
                    \Epicor\OrderApproval\Api\Data\LinkInterface::BY_CUSTOMER     => 0,
                    \Epicor\OrderApproval\Api\Data\LinkInterface::BY_GROUP        => 1,
                ],
                \Epicor\OrderApproval\Api\Data\LinkInterface::class);
            $hierarchyRepository->save($link);
        }
    }

    /**
     * @return string
     */
    private function getGroupId()
    {
        return $this->groupId;
    }

}
