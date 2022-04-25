<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Create\Tab;


class Erpinfo extends \Epicor\Common\Block\Adminhtml\Form\AbstractBlock
{

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $storeSystemStore;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $storeSystemStore,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = [])
    {
        $this->formFactory = $formFactory;
        $this->backendSession = $context->getBackendSession();
        $this->storeSystemStore = $storeSystemStore;
        parent::__construct($context, $data);
        $this->_title = 'Details';
    }

    protected function _prepareForm()
    {
        $accountOption = array();
        $options_account = \Epicor\Comm\Model\Customer\Erpaccount::$All_ErpAcc_links;
        
        foreach ($options_account as $val){
            $accountOption [] = array(
                'value' => $val['link'],
                'label' => $val['type']
            );
        }
        $form = $this->formFactory->create();

        $fieldset = $form->addFieldset('details', array('legend' => __('Account Details')));
        
        $fieldset->addField(
            'account_type', 'select', array(
            'label' => __('Account Type'),
            'required' => true,
            'values' => $accountOption,
            'name' => 'account_type'
            )
        );
        
        $fieldset->addField('name', 'text', array(
            'label' => __('Name'),
            'required' => true,
            'name' => 'name'
        ));

        $fieldset->addField('email', 'text', array(
            'label' => __('Email'),
            'required' => true,
            'name' => 'email',
            'class' => 'validate-email',
        ));

        $fieldset->addField(
            'store', 'select', array(
            'label' => __('Store'),
            'required' => true,
            'values' => $this->_getStores(),
            'name' => 'store'
            )
        );

        $data = $this->backendSession->getFormData();
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Gets an array of visible stores, for display in a select box (optgroup nested)
     * 
     * @return array - array of stores
     */
    private function _getStores()
    {
        $storeModel = $this->storeSystemStore;

        $options = array();

        foreach ($storeModel->getWebsiteCollection() as $website) {
            $websiteShow = false;
            $groupOptions = array();

            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }

                $websiteShow = true;
                $groupOptions[] = array(
                    'label' => $group->getName(),
                    'value' => 'store_' . $group->getDefaultStoreId()
                );
            }

            if ($websiteShow) {
                $options[] = array(
                    'label' => $website->getName(),
                    'value' => 'website_' . $website->getId()
                );

                if (!empty($groupOptions)) {
                    $options[] = array(
                        'label' => 'Store Groups',
                        'value' => $groupOptions
                    );
                }
            }
        }

        return $options;
    }

}
