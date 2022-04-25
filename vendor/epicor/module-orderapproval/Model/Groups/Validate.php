<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\Groups;

use Magento\Backend\Helper\Js as BackendJsHelper;
use Epicor\OrderApproval\Model\HierarchyManagement as HierarchyManagement;
use Epicor\OrderApproval\Model\GroupsRepository as GroupsRepository;
use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;
use Epicor\OrderApproval\Model\Groups;

/**
 * Model Class for OrderApproval
 *
 * @category   Epicor
 * @package    Epicor_OrderApproval
 * @author     Epicor Websales Team
 *
 */
class Validate
{

    /**
     * @var BackendJsHelper
     */
    private $backendJsHelper;

    /**
     * @var HierarchyManagement
     */
    private $hierarchyManagement;

    /**
     * @var GroupsRepository
     */
    private $groupsRepository;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Validate constructor.
     *
     * @param BackendJsHelper     $backendJsHelper
     * @param HierarchyManagement $hierarchyManagement
     * @param GroupsRepository    $groupsRepository
     * @param Serializer          $serializer
     */
    public function __construct(
        BackendJsHelper $backendJsHelper,
        HierarchyManagement $hierarchyManagement,
        GroupsRepository $groupsRepository,
        Serializer $serializer
    ) {
        $this->backendJsHelper = $backendJsHelper;
        $this->hierarchyManagement = $hierarchyManagement;
        $this->groupsRepository = $groupsRepository;
        $this->serializer = $serializer;
    }

    /**
     * @param Groups $groups
     * @param array  $data
     *
     * @return array|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isValid($groups, &$data)
    {
        $errors = array();
        $groupName = $groups->getName();
        if (empty($groupName)) {
            $errors[] = __('Group name must not be empty');
        }

        if (isset($data['hierarchy']) && isset($data['hierarchy']['parent'])
            && isset($data['hierarchy']['children'])
        ) {
            $childes
                = array_keys($this->backendJsHelper->decodeGridSerializedInput($data['hierarchy']['children']));

            if (in_array($data['hierarchy']['parent'], $childes)) {
                $errors[]
                    = __("Hierarchy doesn't allow same parent and children.");
            }
        }

        //Validate parent selection or rule total is lesser than parent rule total.
        if (!$this->validateParentTotal($groups, $data)) {
            $errors[]
                = __("Wrong parent select or rule total should be lesser than parent rule total.");
        }

        //Validate Children selection or rule total is lesser than parent rule total.
        if (!$this->validateChildrenTotal($groups, $data)) {
            $errors[]
                = __("Wrong Children select or rule total should be higher than children rule total.");
        }

        //Validate Children selection or rule total is lesser than parent rule total.
        if (!$this->validateBudgetType($data)) {
            $errors[] = __("Budget Type Should Be Unique.");
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * @param Groups $groups
     * @param array  $data
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validateParentTotal($groups, $data)
    {
        $parentGroupTotal = 0;
        if (isset($data['hierarchy']) && isset($data['hierarchy']['parent'])) {
            $parentId = $data['hierarchy']['parent'];
            $patentGroups = $this->groupsRepository->getById($parentId);
            $parentGroupTotal = $this->getRuleTotal(
                $patentGroups->getRules(),
                false
            );
        } elseif (!$groups->isObjectNew()) {
            $parentCollection
                = $this->hierarchyManagement->getCollectionByParentId($groups->getId());
            if ($parentCollection->count() > 0) {
                $parentRules = $parentCollection->getFirstItem()->getRules();
                $parentGroupTotal = $this->getRuleTotal($parentRules, false);
            }
        }

        if (!$parentGroupTotal) {
            return true;
        }

        $groupTotal = 0;
        if (isset($data['group']) && isset($data['group']['rules'])
            && $data['group']['rules']
        ) {
            $groupTotal = $this->getRuleTotal($data['group']['rules'], false);
        } elseif ($groups->getRules()) {
            $groupTotal = $this->getRuleTotal($groups->getRules(), false);
        }

        if ($groupTotal < $parentGroupTotal) {
            return true;
        }

        return false;
    }

    /**
     * @param Groups $groups
     * @param array  $data
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validateChildrenTotal($groups, $data)
    {
        $groupTotal = 0;
        if (isset($data['group']) && isset($data['group']['rules'])
            && $data['group']['rules']
        ) {
            $groupTotal = $this->getRuleTotal($data['group']['rules'], false);
        } elseif ($groups->getRules()) {
            $groupTotal = $this->getRuleTotal($groups->getRules(), false);
        }

        if (!$groupTotal) {
            return true;
        }

        return $this->validateTotal($groups, $data, $groupTotal);
    }

    /**
     * Validate Total.
     *
     * @param Groups $groups
     * @param array  $data
     * @param string $groupTotal
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function validateTotal($groups, $data, $groupTotal)
    {
        if (isset($data['hierarchy'])
            && isset($data['hierarchy']['children'])
        ) {
            return $this->isChildTotalValidate($data, $groupTotal);
        } elseif (!$groups->isObjectNew()) {

            return $this->isParentTotalValidate($groups, $groupTotal);
        }

        return true;
    }

    /**
     * @param array $data
     * @param string $groupTotal
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isChildTotalValidate($data, $groupTotal)
    {
        $childrenIds = array_keys(
            $this->backendJsHelper->decodeGridSerializedInput(
                $data['hierarchy']['children']
            )
        );

        foreach ($childrenIds as $value) {
            $childrenGroups = $this->groupsRepository->getById($value);
            $childrenGroupTotal = $this->getRuleTotal(
                $childrenGroups->getRules(),
                false
            );

            if ($childrenGroupTotal && $groupTotal < $childrenGroupTotal) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $data
     * @param string $groupTotal
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isParentTotalValidate($groups, $groupTotal)
    {
        $childrenCollection = $this->hierarchyManagement
            ->getChildrenCollection($groups->getId());

        if ($childrenCollection->count() > 0) {
            foreach ($childrenCollection->getItems() as $value) {
                $parentGroupTotal = $this->getRuleTotal(
                    $value->getRules(),
                    false
                );

                if ($parentGroupTotal && $groupTotal < $parentGroupTotal) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $conditions
     * @param bool   $isSerialize
     *
     * @return int|mixed
     */
    public function getRuleTotal($conditions, $isSerialize = true)
    {
        if (!$isSerialize) {
            $conditions = $this->serializer->unserialize($conditions);
        }

        if (isset($conditions['conditions'])) {
            foreach ($conditions['conditions'] as $value) {
                if (isset($value["attribute"])
                    && isset($value["value"])
                    && $value["attribute"] == "total"
                    && $value["value"]
                ) {
                    return $value["value"] > 0 ? $value["value"] : 0;
                }
            }
        }

        return 0;
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validateBudgetType(&$data)
    {
        $return = true;
        if (isset($data["budget"]) && isset($data["budget"]["budget"])
            && (count($data["budget"]["budget"]) > 1)
        ) {
            $type = [];
            foreach ($data["budget"]["budget"] as $budget) {
                if (!in_array($budget['type'], $type)) {
                    $type[] = $budget['type'];
                } else {
                    $return = false;
                }
            }
        }

        return $return;
    }
}
