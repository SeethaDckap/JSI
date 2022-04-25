<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Customer;

use Epicor\Lists\Model\ListModel\Type\Productgroup;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Epicor\Lists\Helper\Data
     */
    protected $listsHelper;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;
    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    private $listModelFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Epicor\Lists\Model\ListModelFactory $listModelFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        parent::__construct(
            $context,
            $data
        );
        $this->listModelFactory = $listModelFactory;
        $this->registry = $registry;
    }


    protected function _prepareForm()
    {
        if ($this->isProductGroupListInstance()) {
            $this->addCustomerExcludeCheckbox();
        }

        return parent::_prepareForm();
    }

    private function addCustomerExcludeCheckbox()
    {
        $form = $this->formFactory->create();

        $fieldset = $form->addFieldset('customer_exclude_form', array('legend' => __('Customers')));

        $fieldset->addField('erp_customers_exclusion', 'checkbox', array(
            'label' => __('Exclude selected Customer?'),
            'onclick' => 'this.value = this.checked ? 1 : 0;',
            'name' => 'customer_exclusion',
            'checked' => $this->isChecked()
        ));

        $data['erp_customers_exclusion'] = $this->getListCustomerExclusion();
        $form->setValues($data);
        $this->setForm($form);
    }

    private function isChecked()
    {
        $exclusion = $this->getListCustomerExclusion();
        if ($exclusion === 'Y') {
            return true;
        }

        return false;
    }

    private function getListCustomerExclusion()
    {
        if ($listInstance = $this->getListInstance()) {
            return $listInstance->getCustomerExclusion();
        }
    }

    private function isProductGroupListInstance()
    {
        return $this->getListInstance() instanceof Productgroup;
    }

    private function getListInstance()
    {
        /** @var \Epicor\Lists\Model\ListModel $list */
        $list = $this->registry->registry('list');

        if ($list instanceof \Epicor\Lists\Model\ListModel) {
            return $list->getTypeInstance();
        }
    }
}
