<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access\Right\Edit;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * Initialize Tabs
     *
     */
    // protected $_attributeTabBlock = 'epicor_common/block_adminhtml_access_right_edit_tab_details';


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
        $this->setDestElementId('edit_form'); // this should be same as the form id define above
        $this->setTitle('Access Right');
    }

    protected function _beforeToHtml()
    {
        $block = $this->getLayout()->createBlock('\Epicor\Common\Block\Adminhtml\Access\Right\Edit\Tab\Details');
        $right = $this->registry->registry('access_right_data');

        $this->addTab('form_details', array(
            'label' => 'General',
            'title' => 'General Information',
            'content' => $block->toHtml(),
        ));

        $this->addTab('form_element', array(
            'label' => 'Elements',
            'title' => 'Elements',
            'url' => $this->getUrl('*/*/elements', array('id' => $right->getId(), '_current' => true)),
            'class' => 'ajax',
        ));

        $this->addTab('form_groups', array(
            'label' => 'Groups',
            'title' => 'Groups',
            'url' => $this->getUrl('*/*/groups', array('id' => $right->getId(), '_current' => true)),
            'class' => 'ajax',
        ));

        return parent::_beforeToHtml();
    }

}
