<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locations\Edit;


class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

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
        $this->setTitle('Location');
    }

    protected function _beforeToHtml()
    {
        $location = $this->registry->registry('location');
        /* @var $location Epicor_Comm_Model_Location */

        $detailsBlock = $this->getLayout()->createBlock('\Epicor\Comm\Block\Adminhtml\Locations\Edit\Tab\Details');

        $this->addTab('form_details', array(
            'label' => 'Details',
            'title' => 'Details',
            'content' => $detailsBlock->initForm()->toHtml(),
        ));

        if ($location->getId()) {
            $this->addTab('form_stores', array(
                'label' => 'Stores',
                'title' => 'Stores',
                'url' => $this->getUrl('*/*/stores', array('id' => $location->getId(), '_current' => true)),
                'class' => 'ajax',
            ));


            $this->addTab('form_erp_accounts', array(
                'label' => 'ERP Accounts',
                'title' => 'ERP Accounts',
                'url' => $this->getUrl('*/*/erpaccounts', array('id' => $location->getId(), '_current' => true)),
                'class' => 'ajax'
            ));

            $this->addTab('customers', array(
                'label' => 'Customers',
                'title' => 'Customers',
                'url' => $this->getUrl('*/*/customers', array('id' => $location->getId(), '_current' => true)),
                'class' => 'ajax'
            ));

            $this->addTab('products', array(
                'label' => 'Products',
                'title' => 'Products',
                'url' => $this->getUrl('*/*/products', array('id' => $location->getId(), '_current' => true)),
                'class' => 'ajax'
            ));
            $this->addTab('relatedlocations', array(
                'label' => 'Related Locations',
                'title' => 'Related Locations',
                'url' => $this->getUrl('*/*/relatedlocations', array('id' => $location->getId(), '_current' => true)),
                'class' => 'ajax'
            ));
            $this->addTab('groups', array(
                'label' => 'Groups',
                'title' => 'Groups',
                'url' => $this->getUrl('*/*/groups', array('id' => $location->getId(), '_current' => true)),
                'class' => 'ajax'
            ));
            $this->addTab('form_log', array(
                'label' => 'Message Log',
                'title' => 'Message Log',
                'url' => $this->getUrl('*/*/loggrid', array('id' => $location->getId(), '_current' => true)),
                'class' => 'ajax'
            ));
        }

        return parent::_beforeToHtml();
    }

}
