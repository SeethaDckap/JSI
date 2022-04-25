<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit;


/**
 * List edit tabs
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Epicor\Lists\Model\ListModel
     */
    private $_list;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $jsonEncoder,
            $authSession,
            $data
        );
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('List');
    }

    protected function _beforeToHtml()
    {
        $list = $this->getList();
        /* @var $list Epicor_Lists_Model_ListModel */


        $this->addTab('details', array(
            'label' => 'Details',
            'title' => 'Details',
            'content' => $this->getLayout()->createBlock('\Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Details')->toHtml(),
        ));



        if ($list->getId()) {
            $typeInstance = $list->getTypeInstance();

//            if (Mage::app()->isSingleStoreMode() == false && $typeInstance->isSectionVisible('labels')) {
//                $this->addTab('labels', array(
//                    'label' => 'Labels',
//                    'title' => 'Labels',
//                    'url' => $this->getUrl('*/*/labels', array('id' => $list->getId(), '_current' => true)),
//                    'class' => 'ajax',
//                ));
//            }

            if ($typeInstance->isSectionVisible('erpaccounts')) {
                $this->addTab('erpaccounts', array(
                    'label' => 'ERP Accounts',
                    'title' => 'ERP Accounts',
                    'url' => $this->getUrl('*/*/erpaccounts', array('id' => $list->getId(), '_current' => true)),
                    'class' => 'ajax',
                ));
            }

            if ($typeInstance->isSectionVisible('brands')) {
                $this->addTab('brands', array(
                    'label' => 'Brands',
                    'title' => 'Brands',
                    'url' => $this->getUrl('*/*/brands', array('id' => $list->getId(), '_current' => true)),
                    'class' => 'ajax',
                ));
            }
            //M1 > M2 Translation Begin (Rule P2-6.8)
            //if ((Mage::app()->isSingleStoreMode() == false) && $typeInstance->isSectionVisible('websites')) {
            if (($this->_storeManager->isSingleStoreMode() == false) && $typeInstance->isSectionVisible('websites')) {
            //M1 > M2 Translation End
                $this->addTab('websites', array(
                    'label' => 'Websites',
                    'title' => 'Websites',
                    'url' => $this->getUrl('*/*/websites', array('id' => $list->getId(), '_current' => true)),
                    'class' => 'ajax',
                ));
            }

            //M1 > M2 Translation Begin (Rule P2-6.8)
            //if ((Mage::app()->isSingleStoreMode() == false) && $typeInstance->isSectionVisible('stores')) {
            if (($this->_storeManager->isSingleStoreMode() == false) && $typeInstance->isSectionVisible('stores')) {
            //M1 > M2 Translation End
                $this->addTab('stores', array(
                    'label' => 'Stores',
                    'title' => 'Stores',
                    'url' => $this->getUrl('*/*/stores', array('id' => $list->getId(), '_current' => true)),
                    'class' => 'ajax',
                ));
            }

            if ($typeInstance->isSectionVisible('customers')) {
                $this->addTab('customers', array(
                    'label' => 'Customers',
                    'title' => 'Customers',
                    'url' => $this->getUrl('*/*/customers', array('id' => $list->getId(), '_current' => true)),
                    'class' => 'ajax',
                ));
            }

            if ($typeInstance->isSectionVisible('products')) {
                $this->addTab('products', array(
                    'label' => 'Products',
                    'title' => 'Products',
                    'url' => $this->getUrl('*/*/products', array('id' => $list->getId(), '_current' => true)),
                    'class' => 'ajax',
                ));
            }

            if ($typeInstance->isSectionVisible('addresses')) {
                $this->addTab('addresses', array(
                    'label' => 'Addresses',
                    'title' => 'Addresses',
                    'url' => $this->getUrl('*/*/addresses', array('id' => $list->getId(), '_current' => true)),
                    'class' => 'ajax',
                ));
            }

            if ($list->getType() == 'Rp') {
                //if (1) {
                $this->addTab('restrictions', array(
                    'label' => 'Restrictions',
                    'title' => 'Restrictions',
                    'url' => $this->getUrl('*/*/restrictions', array('id' => $list->getId(), '_current' => true)),
                    'class' => 'ajax',
                ));
            }

            if ($typeInstance->isSectionVisible('messagelog')) {
                $this->addTab('messagelog', array(
                    'label' => 'Message Log',
                    'title' => 'Message Log',
                    'url' => $this->getUrl('*/*/messagelog', array('id' => $list->getId(), '_current' => true)),
                    'class' => 'ajax',
                ));
            }
        }

        return parent::_beforeToHtml();
    }

    /**
     * Gets the current List
     *
     * @return \Epicor\Lists\Model\ListModel
     */
    public function getList()
    {
        if (!$this->_list) {
            $this->_list = $this->registry->registry('list');
        }
        return $this->_list;
    }

}
