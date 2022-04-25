<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Block\Adminhtml\Roles\Edit\Tab\Customer;

use Epicor\Lists\Model\ListModel\Type\Productgroup;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        parent::__construct(
            $context,
            $data
        );
        $this->registry = $registry;
    }


    /**
     * @return \Magento\Backend\Block\Widget\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $role = $this->registry->registry('role');
        /* @var $role \Epicor\AccessRight\Model\RoleModel */

        $form = $this->formFactory->create();
        $fieldset = $form->addFieldset('customers_form', array('legend' => __('Customers')));

        $checked = $role->getCustomerExclusion() == 'Y' ? true : false;
        $fieldset->addField('customer_exclusion', 'checkbox', array(
            'label' => __('Exclude selected Customer?'),
            'onclick' => 'this.value = this.checked ? 1 : 0;',
            'name' => 'customer_exclusion',
            'checked' => $checked
        ));

        $conditions = $role->getCustomerConditions();
        $fieldset->addField('is_customers_condition_enabled', 'checkbox', array(
            'label' => __('Customer to role conditionally?'),
            'onclick' => 'this.value = this.checked ? 1 : 0;',
            'name' => 'is_customers_condition_enabled',
            'checked' => $conditions ? true : false
        ));
        $data['customers_exclusion'] = $checked;
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
