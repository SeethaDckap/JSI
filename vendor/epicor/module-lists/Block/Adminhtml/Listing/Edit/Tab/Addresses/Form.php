<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Addresses;


/**
 * List ERP Accounts Form
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Form extends \Magento\Backend\Block\Widget\Form
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

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

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Epicor\Lists\Helper\Data $listsHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        $this->listsListModelFactory = $listsListModelFactory;
        $this->listsHelper = $listsHelper;
        parent::__construct(
            $context,
            $data
        );
        $this->_title = 'Addresses';
    }

    /**
     * Gets the List for this tab
     *
     * @return boolean
     */
    public function getList()
    {
        if (!$this->list) {
            if ($this->registry->registry('list')) {
                $this->list = $this->registry->registry('list');
            } else {
                $this->list = $this->listsListModelFactory->create()->load($this->getRequest()->getParam('id'));
            }
        }
        return $this->list;
    }

    /**
     * Builds List ERP Accounts Form
     *
     * @return \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Brands\Form
     */
    protected function _prepareForm()
    {
        $helper = $this->listsHelper;
        /* @var $helper Epicor_Lists_Helper_Data */

        $list = $this->registry->registry('list');
        /* @var $list Epicor_Lists_Model_ListModel */

        if ($list->getTypeInstance()->isSectionEditable('addresses')) {
            $form = $this->formFactory->create();

            $fieldset = $form->addFieldset('addresses_form', array('legend' => $helper->__('Addresses')));

            $fieldset->setHeaderBar(
                '<button title="' . $helper->__('Close') . '" type="button" class="scalable" onclick="listAddress.close();"><span><span><span>' . $helper->__('Close') . '</span></span></span></button>'
            );

            $fieldset->addField('address_post_url', 'hidden', array(
                'name' => 'post_url',
                'value' => $this->getUrl('adminhtml/epicorlists_list/addresspost')
            ));

            $fieldset->addField('address_delete_url', 'hidden', array(
                'name' => 'delete_url',
                'value' => $this->getUrl('adminhtml/epicorlists_list/addressdelete')
            ));

            $fieldset->addField('list_id', 'hidden', array(
                'name' => 'list_id',
                'value' => $this->getList()->getId()
            ));

            $fieldset->addField('address_id', 'hidden', array(
                'name' => 'address_id',
            ));

            $fieldset->addField('address_code', 'text', array(
                'label' => $helper->__('Address Code'),
                'required' => true,
                'name' => 'address_code'
            ));

            $fieldset->addField('address_name', 'text', array(
                'label' => $helper->__('Address Name'),
                'required' => false,
                'name' => 'address_name'
            ));

            $fieldset->addField('address1', 'text', array(
                'label' => $helper->__('Address 1'),
                'required' => false,
                'name' => 'address1'
            ));

            $fieldset->addField('address2', 'text', array(
                'label' => $helper->__('Address 2'),
                'required' => false,
                'name' => 'address2'
            ));

            $fieldset->addField('address3', 'text', array(
                'label' => $helper->__('Address 3'),
                'required' => false,
                'name' => 'address3'
            ));

            $fieldset->addField('city', 'text', array(
                'label' => $helper->__('city'),
                'required' => false,
                'name' => 'city'
            ));

            $fieldset->addField('county', 'text', array(
                'label' => $helper->__('County'),
                'required' => false,
                'name' => 'county'
            ));

            $fieldset->addField('Country', 'text', array(
                'label' => $helper->__('Country'),
                'required' => false,
                'name' => 'Country'
            ));

            $fieldset->addField('telephone_number', 'text', array(
                'label' => $helper->__('Telephone Number'),
                'required' => false,
                'name' => 'telephone_number'
            ));

            $fieldset->addField('mobile_number', 'text', array(
                'label' => $helper->__('Mobile Number'),
                'required' => false,
                'name' => 'mobile_number'
            ));

            $fieldset->addField('fax_number', 'text', array(
                'label' => $helper->__('Fax Number'),
                'required' => false,
                'name' => 'fax_number'
            ));

            $fieldset->addField('email_address', 'text', array(
                'label' => $helper->__('Email Address'),
                'required' => false,
                'name' => 'email_address'
            ));

            $fieldset->addField('addSubmit', 'submit', array(
                'value' => $helper->__('Add'),
                'onclick' => "return listAddress.save();",
                'name' => 'addSubmit',
                'class' => 'form-button'
            ));

            $fieldset->addField('updateSubmit', 'submit', array(
                'value' => $helper->__('Update'),
                'onclick' => "return listAddress.save();",
                'name' => 'updateSubmit',
                'class' => 'form-button'
            ));

            $this->setForm($form);
        }

        return parent::_prepareForm();
    }

}
