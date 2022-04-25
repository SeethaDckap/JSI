<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Model\GroupSave;

use Epicor\OrderApproval\Api\GroupsRepositoryInterface;
use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;
use Epicor\OrderApproval\Model\GroupSave\Utilities;
use Epicor\OrderApproval\Model\GroupSave\Groups as GroupSave;
use Epicor\OrderApproval\Model\Groups as ApprovalGroups;

class Rules
{
    const UNLIMITED_APPROVAL_AMOUNT_VALUE = '0';

    const APPROVAL_LIMIT_ORDER_VALUE_TYPE = 'Epicor\OrderApproval\Model\Rules\OrderValue';

    const APPROVAL_LIMIT_CONDITION_TYPE = 'Epicor\OrderApproval\Model\Rules\Condition';

    const ORDER_VALUE_ATTRIBUTE_TYPE = 'approval_limit';

    const CONDITION_ATTRIBUTE_TYPE = 'total';

    /**
     * @var GroupsRepositoryInterface
     */
    private $groupsRepository;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var \Epicor\OrderApproval\Model\GroupSave\Utilities
     */
    private $utilities;
    /**
     * @var Groups
     */
    private $groupSave;

    /**
     * Rules constructor.
     * @param GroupsRepositoryInterface $groupsRepository
     * @param Serializer $serializer
     * @param \Epicor\OrderApproval\Model\GroupSave\Utilities $utilities
     * @param Groups $groupSave
     */
    public function __construct(
        GroupsRepositoryInterface $groupsRepository,
        Serializer $serializer,
        Utilities $utilities,
        GroupSave $groupSave
    ) {
        $this->groupsRepository = $groupsRepository;
        $this->serializer = $serializer;
        $this->utilities = $utilities;
        $this->groupSave = $groupSave;
    }

    /**
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveApprovalLimit()
    {
        $approvalLimitData = $this->getApprovalLimitSerialized();

        if ($this->isEmptyRuleSetForNewGroup() || $this->isEmptyRuleSetForUpdatedGroup()) {
            $approvalLimitData = $this->getSerializedUnlimitedApprovalRule();
        }
        if (!$approvalLimitData) {
            return false;
        }
        $group = $this->groupSave->getMainGroup();
        if ($group instanceof ApprovalGroups) {
            $group->setData('rules', $approvalLimitData);
            $this->groupsRepository->save($group);
        }
    }

    /**
     * @param $rules
     * @return mixed|string
     */
    public function getApprovalLimitFromRule($rules)
    {
        if (!$rules) {
            return false;
        }
        $unSerializedRule = $this->serializer->unserialize($rules);
        $this->setRuleTypeAttributes($unSerializedRule);
        $rulesData = $unSerializedRule['conditions'] ?? [];

        foreach ($rulesData as $rule) {
            $attribute = $rule['attribute'] ?? '';
            $value = $rule['value'] ?? '';
            if ($attribute === 'approval_limit' && $value !== '') {
                return $value;
            }
        }
    }

    /**
     * @param $unSerializedRule
     */
    public function setRuleTypeAttributes(&$unSerializedRule)
    {
        if ($this->isConditionTypeRule($unSerializedRule)) {
            $unSerializedRule['conditions'][0]['type'] = self::APPROVAL_LIMIT_ORDER_VALUE_TYPE;
            $unSerializedRule['conditions'][0]['attribute'] = self::ORDER_VALUE_ATTRIBUTE_TYPE;
        }
    }

    /**
     * @param $unSerializedRule
     * @return bool
     */
    private function isConditionTypeRule($unSerializedRule)
    {
        return isset($unSerializedRule['conditions'][0]['type'])
            && isset($unSerializedRule['conditions'][0]['attribute'])
            && $unSerializedRule['conditions'][0]['type'] === self::APPROVAL_LIMIT_CONDITION_TYPE;
    }

    private function isEmptyRuleSetForNewGroup()
    {
        return !$this->getApprovalLimitSerialized() && $this->groupSave->isNewGroup();
    }

    private function isEmptyRuleSetForUpdatedGroup()
    {
        return !$this->getApprovalLimitSerialized() && $this->isRulesSelected() && !$this->groupSave->isNewGroup();
    }

    private function isRulesSelected()
    {
        $data = $this->utilities->getPostData();
        return (boolean) $data['selected_rule'] ?? false;
    }

    /**
     * @return bool|string
     */
    private function getApprovalLimitSerialized()
    {
        $approvalLimitValue = $this->utilities->getPostData()['approval_limit']['conditions']['1--1']['value'] ?? null;
        if ($approvalLimitValue !== null) {
            $ruleCondition = $this->buildRuleData($approvalLimitValue);
            return $this->serializer->serialize($ruleCondition);
        }

        return null;
    }

    private function buildRuleData($value)
    {
        return [
            'conditions' => [
                0 => [
                    'type' => self::APPROVAL_LIMIT_CONDITION_TYPE,
                    'attribute' => self::CONDITION_ATTRIBUTE_TYPE,
                    'operator' => '==',
                    'value' => $value
                ]
            ]
        ];
    }

    /**
     * @return bool|string
     */
    private function getSerializedUnlimitedApprovalRule()
    {
        $rule = $this->buildRuleData(self::UNLIMITED_APPROVAL_AMOUNT_VALUE);

        return $this->serializer->serialize($rule);
    }
}
