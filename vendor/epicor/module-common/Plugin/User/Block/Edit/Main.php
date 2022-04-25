<?php
/**
 * Copyright © 2010-2021 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Plugin\User\Block\Edit;

use Epicor\Common\Helper\User as UserHelper;

/**
 * Class Main
 * @package Epicor\Common\Plugin\User\Block\Edit
 */
class Main
{
    /**
     * @var UserHelper
     */
    private $userHelper;

    /**
     * Main constructor.
     * @param UserHelper $userHelper
     */
    public function __construct(
        UserHelper $userHelper
    ) {
        $this->userHelper = $userHelper;
    }

    /**
     * Adding Password Strength Meter to Password Field
     * @param \Magento\User\Block\User\Edit\Tab\Main $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundGetFormHtml(
        \Magento\User\Block\User\Edit\Tab\Main $subject,
        \Closure $proceed
    ) {
        $form = $subject->getForm();
        if (is_object($form)) {
            $fieldset = $form->getElement('base_fieldset');
            $field = $form->getElement('password');
            $passwordLabel = $field->getLabel()->getText();
            $isRequired = $field->getRequired();
            $validationClass = $field->getClass();
            $validationClass = str_replace('validate-admin-password', 'validate-customer-password', $validationClass);
            $fieldset->removeField('password');
            $fieldset->addField(
                'password',
                'password',
                [
                    'name' => 'password',
                    'label' => $passwordLabel,
                    'id' => 'customer_pass',
                    'title' => $passwordLabel,
                    'class' => $validationClass,
                    'required' => $isRequired,
                    'data-password-min-length' => $this->userHelper->getMinPasswordLength(),
                    'data-password-min-character-sets' => $this->userHelper->getRequiredClassNumber(),
                    'after_element_html' => $this->userHelper->getPasswordMeterHtml(),
                    'no_wrap_as_addon' => true
                ],
                'email'
            );
            $subject->setForm($form);
        }
        return $proceed();
    }
}