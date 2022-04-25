<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Block\Adminhtml\Roles\Edit\Tab\Erpaccounts;

class Conditions extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $conditions;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Epicor\Comm\Model\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * Conditions constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     * @param \Epicor\Comm\Model\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [],
        \Epicor\Comm\Model\Serialize\Serializer\Json $serializer = null
    ) {
        $this->formFactory = $formFactory;
        $this->rendererFieldset = $rendererFieldset;
        $this->registry = $registry;
        $this->conditions = $conditions;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Epicor\Comm\Model\Serialize\Serializer\Json::class
        );
        parent::__construct($context, $data);
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
     * Prepare form before rendering HTML
     *
     *
     */
    protected function _prepareForm()
    {
        //$model = $this->_coreRegistry->registry('current_rule');
        $role = $this->registry->registry('role');
        /* @var $role \Epicor\AccessRight\Model\RoleModel */

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('*/*/condition/form/rule_erp_conditions_fieldset', array('view_type' => 'erpaccounts'))
        );

        $fieldset = $form->addFieldset(
            'erp_conditions_fieldset',
            [
                'legend' => __(
                    'Apply the rule only if the following conditions are met (leave blank for all ERP accounts).'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $erpAccountModel = $role->getErpAccountModel();
        //load condition data
        if($erpConditions = $role->getErpAccountsConditions()) {
            $unSerializeCondition = $this->serializer->unserialize($erpConditions);
            $erpAccountModel->getConditions()->setConditions([])->loadArray($unSerializeCondition);
        }

        $fieldset->addField(
            'erp_accounts_conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $erpAccountModel
        )->setRenderer(
            $this->conditions
        );

        $form->setValues($role->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}