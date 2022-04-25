<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\OrderApproval\Block\Group\Rules;

use Epicor\OrderApproval\Model\Rules\FrontEnd\ConditionFactory as RulesConditionFactory;
use Epicor\OrderApproval\Model\GroupSave\Rules as GroupRules;

class Conditions extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    private $rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    private $conditions;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var RulesConditionFactory
     */
    private $rulesConditionFactory;

    /**
     * @var \Epicor\OrderApproval\Api\GroupsRepositoryInterface
     */
    private $groupsRepository;

    /**
     * @var \Epicor\OrderApproval\Model\Groups
     */
    private $groups;
    /**
     * @var GroupRules
     */
    private $groupRules;

    /**
     * Conditions constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param RulesConditionFactory $rulesConditionFactory
     * @param \Epicor\Comm\Model\Serialize\Serializer\Json $serializer
     * @param \Epicor\OrderApproval\Api\GroupsRepositoryInterface $groupsRepository
     * @param \Epicor\OrderApproval\Model\Groups $groups
     * @param GroupRules $groupRules
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Framework\Data\FormFactory $formFactory,
        RulesConditionFactory $rulesConditionFactory,
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer,
        \Epicor\OrderApproval\Api\GroupsRepositoryInterface $groupsRepository,
        \Epicor\OrderApproval\Model\Groups $groups,
        GroupRules $groupRules,
        array $data = []
    ) {
        $this->setTemplate('Epicor_OrderApproval::rules/form.phtml');
        $this->formFactory = $formFactory;
        $this->rendererFieldset = $rendererFieldset;
        $this->conditions = $conditions;

        parent::__construct($context, $data);
        $this->rulesConditionFactory = $rulesConditionFactory;
        $this->serializer = $serializer;
        $this->groupsRepository = $groupsRepository;
        $this->groups = $groups;
        $this->groupRules = $groupRules;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Conditions
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->rendererFieldset->setTemplate(
            'Epicor_OrderApproval::rules/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('*/*/condition/form/rule_approval_conditions_fieldset', ['view_type' => 'approval-limit'])
        );

        $fieldset = $form->addFieldset(
            'approval_conditions_fieldset',
            [
                'legend' => __(
                    'Order approval conditions.'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $rule = $this->rulesConditionFactory->create();
        $rule->setData('group_id', $this->getGroupId());


        //load condition data

        $conditions = $this->getCurrentApprovalConditions();

        if ($conditions) {
            $unSerializeCondition = $this->serializer->unserialize($conditions);
            $this->groupRules->setRuleTypeAttributes($unSerializeCondition);
            $rule->getConditions()->setConditions([])->loadArray($unSerializeCondition);
        }

        $fieldset->addField(
            'approval_accounts_conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $rule
        )->setRenderer(
            $this->conditions
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCurrentApprovalConditions()
    {
        if ($groupId = $this->getGroupId()) {
            $group = $this->groupsRepository->getById($groupId);
            return $group->getRules();
        }
    }


    /**
     * @return mixed
     */
    private function getGroupId()
    {
        return $this->getRequest()->getParam('id');
    }
}
