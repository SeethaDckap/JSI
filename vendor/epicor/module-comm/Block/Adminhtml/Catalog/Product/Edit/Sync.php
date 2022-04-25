<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Catalog\Product\Edit;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Sync extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function getAjaxUrl()
    {
        $product = $this->registry->registry('current_product');
        $params = array('product' => $product->getId());
        return $this->getUrl('adminhtml/epicorcomm_message_ajax/syncftpimages', $params);
    }

    public function getEditUrl()
    {
        return $this->getUrl('*/*/edit', array(
                '_current' => true,
                'tab' => 'product_info_tabs_group_10',
                'active_tab' => null
        ));
    }
    
    /**
     * Get Ajax Url to Sync Related Document
     * 
     * @return string
     */
    public function getAjaxSyncRelatedDocUrl()
    {
        $product = $this->registry->registry('current_product');
        $params = array('product' => $product->getId());
        return $this->getUrl('adminhtml/epicorcomm_message_ajax/syncRelatedDocs',$params);
    }

}
