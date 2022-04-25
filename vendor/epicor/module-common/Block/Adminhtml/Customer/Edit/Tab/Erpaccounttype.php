<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


namespace Epicor\Common\Block\Adminhtml\Customer\Edit\Tab;


class Erpaccounttype extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Ui\Component\Layout\Tabs\TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        //prepare form field.
        $this->setForm($form);

        $fieldset = $form->addFieldset('layout_block_form', array('legend' => __('Erp Account Type')));

        $fieldset->addType('account_selector', 'Epicor\Common\Block\Adminhtml\Form\Element\Erpaccounttype');

        $disabled = false;
        $customer = $this->getCustomer();
        if ($customer && !$customer->isObjectNew()) {
            $erpCount = $customer->getErpAcctCounts();
            if(!empty($erpCount) && count($erpCount) > 1){
                $disabled = true;
            }
        }
        if($disabled){
            $fieldset->addField('ecc_erp_account_type','account_selector',[
                'label' => __('ERP Account Type'),
                'name' => 'customer[ecc_erp_account_type]',
                'class' =>'select admin__control-select',
                'data-form-part' => 'customer_form',
                'disabled' => true
            ]);
        }else{
            $fieldset->addField('ecc_erp_account_type','account_selector',[
                'label' => __('ERP Account Type'),
                'name' => 'customer[ecc_erp_account_type]',
                'class' =>'select admin__control-select',
                'data-form-part' => 'customer_form'
            ]);
        }
        $masterShopperValue = $customer->getEccMasterShopper();
        
        $fieldset->addField('ecc_master_shopper','select',[
            'label' => __('Master Shopper'),
            'name' => 'customer[ecc_master_shopper]',
           // 'options' => array('1' => 'Yes', '0' => 'No'),
            'values'=>array('1' => 'Yes', '0' => 'No'), 
            'value'=>array($masterShopperValue),
            'data-form-part' => 'customer_form'
        ]); 
       
        $this->setForm($form);

        return parent::_prepareForm();
    }


    private function getCustomer()
    {

        $customerId = $this->registry->registry('current_customer_id');
        $customer = $this->registry->registry('current_customer');
        if (!$customer) {
            $customer = $this->customerFactory->create()->load($customerId);
            $this->registry->register('current_customer', $customer);
        }

        return $customer;
    }

    public function getTabLabel()
    {
        return __('Erp Account Type');
    }

    public function getTabTitle()
    {
        return __('Erp Account Type');
    }

    public function getTabClass()
    {
        return 'ajax';
    }

    public function getTabUrl()
    {
        return $this->getUrl('adminhtml/epicorcommon_customer/erpaccounttype', array('_current' => true));
    }

    public function isAjaxLoaded()
    {
        return true;
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
       return false;
    }

}