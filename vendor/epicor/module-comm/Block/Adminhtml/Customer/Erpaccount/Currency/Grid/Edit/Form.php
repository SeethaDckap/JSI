<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Currency\Grid\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('row_data');
        $form = $this->_formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'enctype' => 'multipart/form-data',
                'action' => $this->getData('action'),
                'method' => 'post'
            ]
            ]
        );

        $form->setHtmlIdPrefix('erpcurrency');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Edit Currency Min Order Amount'), 'class' => 'fieldset-wide']
        );
        $fieldset->addField('id', 'hidden', ['name' => 'id']);


        $fieldset->addField(
            'currency_code',
            'text',
            [
                'name' => 'currency_code',
                'label' => __('Currency Code'),
                'id' => 'currency_code',
                'title' => __('Currency Code'),
                'class' => 'required-entry',
                'disabled' => 'disabled',
                'required' => false,
            ]
        );

        $fieldset->addField(
            'min_order_amount',
            'text',
            [
                'name' => 'min_order_amount',
                'label' => __('Min Order Amount'),
                'id' => 'min_order_amount',
                'title' => __('Min Order Amount'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
