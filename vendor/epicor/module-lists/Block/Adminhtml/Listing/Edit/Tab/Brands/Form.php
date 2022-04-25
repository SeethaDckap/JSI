<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Brands;


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
        $this->_title = 'Brands';
    }

    /**
     * Gets the List for this tab
     *
     * @return boolean
     */
    public function getList()
    {
        if (!isset($this->list)) {
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

        $form = $this->formFactory->create();

        $fieldset = $form->addFieldset('brands_form', array('legend' => __('Brands')));

        $fieldset->setHeaderBar(
            '<button title="' . __('Close') . '" type="button" class="scalable" onclick="listBrand.close();"><span><span><span>' . __('Close') . '</span></span></span></button>'
        );

        $fieldset->addField('brand_post_url', 'hidden', array(
            'name' => 'post_url',
            'value' => $this->getUrl('epicor_lists/epicorlists_lists/brandpost')
        ));

        $fieldset->addField('brand_delete_url', 'hidden', array(
            'name' => 'delete_url',
            'value' => $this->getUrl('epicor_lists/epicorlists_lists/branddelete')
        ));

        $fieldset->addField('list_id', 'hidden', array(
            'name' => 'list_id',
            'value' => $this->getList()->getId()
        ));

        $fieldset->addField('brand_id', 'hidden', array(
            'name' => 'brand_id',
        ));

        $fieldset->addField('company', 'text', array(
            'label' => __('Company *'),
            'required' => false,
            'name' => 'company',
            'class' => 'check-empty' //Handling validation in javascript(ChildrenGrid.js),
        ));

        $fieldset->addField('site', 'text', array(
            'label' => __('Site'),
            'required' => false,
            'name' => 'site'
        ));

        $fieldset->addField('warehouse', 'text', array(
            'label' => __('Warehouse'),
            'required' => false,
            'name' => 'warehouse'
        ));

        $fieldset->addField('group', 'text', array(
            'label' => __('Group'),
            'required' => false,
            'name' => 'group'
        ));

        $fieldset->addField('addSubmit', 'submit', array(
            'value' => __('Add'),
            'onclick' => "return listBrand.save();",
            'name' => 'addSubmit',
            'class' => 'form-button'
        ));

        $fieldset->addField('updateSubmit', 'submit', array(
            'value' => __('Update'),
            'onclick' => "return listBrand.save();",
            'name' => 'updateSubmit',
            'class' => 'form-button'
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
