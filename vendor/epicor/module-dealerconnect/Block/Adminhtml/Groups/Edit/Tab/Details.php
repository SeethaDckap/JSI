<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Groups\Edit\Tab;


/**
 * Dealer Group Details Form
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Details extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Epicor\Comm\Model\Config\Source\YesnonulloptionFactory
     */
    protected $commConfigSourceYesnonulloptionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Epicor\Comm\Model\Config\Source\YesnonulloptionFactory $commConfigSourceYesnonulloptionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    )
    {
        $this->formFactory = $formFactory;
        $this->backendSession = $context->getBackendSession();
        $this->customerCustomerFactory = $customerCustomerFactory;
        $this->commConfigSourceYesnonulloptionFactory = $commConfigSourceYesnonulloptionFactory;
        $this->registry = $registry;
        $this->_localeResolver = $localeResolver;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
        $this->_title = 'Details';
    }

    /**
     * Builds Group Details Form
     *
     * @return \Epicor\Dealerconnect\Block\Adminhtml\Groups\Edit\Tab\Details
     */
    protected function _prepareForm()
    {
        $dealerGrp = $this->getDealerGrp();
        /* @var $dealerGrp Epicor_Dealerconnect_Model_Dealergroups */

        $form = $this->formFactory->create();
        $formData = $this->backendSession->getFormData(true);

        if (empty($formData)) {
            $formData = $dealerGrp->getData();
        } else {
            if (is_array($formData)) {
                $dealerGrp->addData($formData);
            }
        }
        $this->addPrimaryFields($form, $dealerGrp);
        $this->addActiveFields($form, $dealerGrp);

        if (!$dealerGrp->isObjectNew()) {
            $this->addErpFields($form, $dealerGrp);
        }

        $formData['settings'] = $dealerGrp->getSettings();
        $formData['erp_override'] = $dealerGrp->getErpOverride();
        $formData['code'] = $dealerGrp->getCode();
        $form->addValues($formData);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Adds Primary fields to the form
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Epicor\Dealerconnect\Model\Dealergroups $dealerGrp
     *
     * @return void
     */
    protected function addPrimaryFields($form, $dealerGrp)
    {
        $fieldset = $form->addFieldset('primary', array('legend' => __('Primary Details')));
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */

        $fieldset->addField(
            'title', 'text', array(
                'label' => __('Title'),
                'required' => true,
                'name' => 'title'
            )
        );

        $args = array();
        if ($dealerGrp->isObjectNew()) {
            $fieldset->addField(
                'listcodeurl', 'hidden', array(
                'name' => 'listcodeurl',
                'value' => $this->getUrl('adminhtml/epicordealer_groups/validateCode', $args)
                )
            );
        }

        $disableFields = $dealerGrp->isObjectNew() == false;
        $fieldset->addField(
            'code', 'text', array(
                'label' => __('Code'),
                'required' => true,
                'name' => 'code',
                'class' => $dealerGrp->isObjectNew() ? 'required-entry validate-list-code' : '',
                'disabled' => $disableFields,
                'note' => __('Unique reference code for this dealer group')
            )
        );

        $fieldset->addField(
            'description', 'textarea', array(
            'label' => __('Description'),
            'required' => false,
            'name' => 'description'
            )
        );
    }

    /**
     * Adds Primary fields to the form
     *
     * @param \Magento\Framework\Data\Form $form
     *
     * @return void
     */
    protected function addActiveFields($form, $dealerGrp)
    {
        $fieldset = $form->addFieldset('active_fields', array('legend' => __('Active Details')));
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */

        $disableEdit = ($this->getDealerGrp()->getType() == "Co") ? true : false;

        $hideActiveUi = false;

        $fieldset->addField(
            'active', 'checkbox', array(
                'label' => __('Is Active?'),
                'tabindex' => 1,
                'value' => 1,
                'name' => 'active',
                'checked' => $this->getDealerGrp()->getActive()
            )
        );
    }


    /**
     * Adds Primary fields to the form
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Epicor\Dealerconnect\Model\dealergroups $dealerGrp
     *
     * @return void
     */
    protected function addErpFields($form, $dealerGrp)
    {
        $typeInstance = $dealerGrp->getTypeInstance();

        if ($typeInstance && $typeInstance->hasErpMsg()) {
            $msgName = $typeInstance->getErpMsg();
            //M1 > M2 Translation Begin (Rule 55)
            //$legend = array('legend' => $this->__('Overwritten On %s Update', $msgName));
            $legend = array('legend' => __('Overwritten On %1 Update', $msgName));
            //M1 > M2 Translation End
            $fieldset = $form->addFieldset('erp_override_fields', $legend);
            /* @var $fieldset Varien_Data_Form_Element_Fieldset */

            $erpOverride = $dealerGrp->getErpOverride();

            $msgSections = $typeInstance->getErpMsgSections();
            foreach ($msgSections as $value => $title) {
                $fieldset->addField('erp_override_' . $value, 'select', array(
                    'label' => $title,
                    'name' => 'erp_override[' . $value . ']',
                    'values' => $this->commConfigSourceYesnonulloptionFactory->create()->toOptionArray(),
                    'value' => isset($erpOverride[$value]) ? $erpOverride[$value] : null
                ));
            }
        }
    }

    /**
     * Gets the current Dealer Group
     *
     * @return \Epicor\Dealerconnect\Model\Dealergroups
     */
    public function getDealerGrp()
    {
        if (!isset($this->_dealerGrp)) {
            $this->_dealerGrp = $this->registry->registry('dealergrp');
        }
        return $this->_dealerGrp;
    }

}
