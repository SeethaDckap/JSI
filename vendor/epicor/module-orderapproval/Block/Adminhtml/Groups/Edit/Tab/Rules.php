<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Adminhtml\Groups\Edit\Tab;

use Epicor\OrderApproval\Api\GroupsRepositoryInterface;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;
use Epicor\OrderApproval\Model\Groups;
use Epicor\OrderApproval\Model\GroupsFactory;
use Magento\Framework\Data\Form as Form;
use Epicor\OrderApproval\Model\RulesFactory;

class Rules extends Generic implements TabInterface
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
     * @var GroupsRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var GroupsFactory
     */
    private $groupsFactory;

    /**
     * @var RulesFactory
     */
    private $rulesFactory;

    /**
     * Rules constructor.
     *
     * @param \Magento\Backend\Block\Template\Context              $context
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Framework\Data\FormFactory                  $formFactory
     * @param \Magento\Rule\Block\Conditions                       $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param GroupsRepositoryInterface                            $groupRepository
     * @param Serializer                                           $serializer
     * @param GroupsFactory                                        $groupsFactory
     * @param RulesFactory                                         $rulesFactory
     * @param array                                                $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        GroupsRepositoryInterface $groupRepository,
        Serializer $serializer,
        GroupsFactory $groupsFactory,
        RulesFactory $rulesFactory,
        array $data = []
    ) {
        $this->rendererFieldset = $rendererFieldset;
        $this->conditions = $conditions;
        $this->groupRepository = $groupRepository;
        $this->serializer = $serializer;
        $this->groupsFactory = $groupsFactory;
        $this->rulesFactory = $rulesFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Rules
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var $model Groups */
        $model = $this->loadEntity();

        /** @var Form $form */
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param Groups $model
     * @param string $fieldsetId
     * @param string $formName
     *
     * @return Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTabToForm($model)
    {
        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('group_');

        $renderer = $this->rendererFieldset->setTemplate(
            'Epicor_OrderApproval::rules/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('*/*/condition/form/rule')
        );

        $fieldset = $form->addFieldset(
            'rule_fieldset',
            [
                'legend' => __(
                    'Apply the rules only if the following conditions are met.'
                ),
            ]
        )->setRenderer(
            $renderer
        );

        /** @var \Epicor\OrderApproval\Model\Rules $ruleModel */
        $ruleModel = $this->rulesFactory->create();
        //load condition data
        if ($erpConditions = $model->getRules()) {
            $unSerializeCondition
                = $this->serializer->unserialize($erpConditions);
            $ruleModel->getConditions()->setConditions([])
                ->loadArray($unSerializeCondition);
        }

        $fieldset->addField(
            'rule',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
            ]
        )->setRule(
            $ruleModel
        )->setRenderer(
            $this->conditions
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return $form;
    }

    /**
     * @param null $groupId
     *
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface|Groups
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function loadEntity($groupId = null)
    {
        $group = null;
        if ( ! $groupId) {
            $groupId = $this->getRequest()->getParam('group_id', null);
        }

        if ($groupId != null) {
            $group = $this->groupRepository->getById($groupId);
            /* @var $group Groups */
        } else {
            $group = $this->groupsFactory->create();
        }

        return $group;
    }

    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Rules');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Rules');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return null;
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }
}
