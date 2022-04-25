<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Delivery;


/**
 * Exclude/Include Delivery Methods Form
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 * Builds ERP Accounts Delivery methods exclude/include Form
 *
 * @return \Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Edit\Tab\Delivery\Form
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Epicor\Comm\Model\Customer\ErpaccountFactory
     */
    protected $commCustomerErpaccountFactory;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    public function __construct(
        \Epicor\Comm\Model\Customer\ErpaccountFactory $commCustomerErpaccountFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Backend\Block\Template\Context $context,
        $data = array())
    {
        $this->formFactory = $formFactory;
        $this->commCustomerErpaccountFactory = $commCustomerErpaccountFactory;
        parent::__construct(
            $context,
            $data);
        $this->setId('valid_payments_form');
    }

    protected function _prepareForm()
    {

        $form = $this->formFactory->create();
        $fieldset = $form->addFieldset('conditions_option', array('legend' => __('Conditions')));

        $erpGroup = $this->commCustomerErpaccountFactory->create()->load($this->getRequest()->getParam('id'));

        if (!(is_null($erpGroup->getAllowedDeliveryMethods()) && is_null($erpGroup->getAllowedDeliveryMethodsExclude()))) {
            $checked = is_null($erpGroup->getAllowedDeliveryMethods()) ? true : false;
            $value = is_null($erpGroup->getAllowedDeliveryMethods()) ? 1 : 0;
        } else {
            $checked = true;
            $value = 1;
        }
        $fieldset->addField('exclude_selected_delivery', 'checkbox', array(
            'label' => __('Exclude selected Delivery Methods?'),
            'onclick' => 'this.value = this.checked ? 1 : 0;',
            'name' => 'exclude_selected_delivery',
            'checked' => $checked,
            'value' => $value
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
