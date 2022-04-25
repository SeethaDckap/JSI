<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Catalog\Category\Edit;


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


    /**
     * Returns ajax url for images sync
     *
     * @return string $ajaxUrl
     */
    public function getAjaxUrl()
    {
           //  $ajax_url=  $this->getUrl('adminhtml/epicorcomm_message_ajax/syncftpimages',$params);
        
        $category = $this->registry->registry('current_category');
        $params = array('category' => $category->getId());
        $ajaxUrl = $this->getUrl('adminhtml/epicorcomm_message_ajax/synccategoryimages', $params);

        return $ajaxUrl;
    }

    /**
     * Returns edit url once image sync is done
     *
     * @return string $editUrl
     */
    public function getEditUrl()
    {
        $category = $this->registry->registry('current_category');
        $params = array('id' => $category->getId());
        $editUrl = $this->getUrl('*/*/edit', $params);

        return $editUrl;
    }

}
