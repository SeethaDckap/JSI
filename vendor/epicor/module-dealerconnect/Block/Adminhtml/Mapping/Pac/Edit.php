<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Mapping\Pac;


class Edit extends  \Epicor\Common\Block\Adminhtml\Mapping\DefaultBlock\Edit
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Widget\Context  $context,
        \Magento\Framework\Registry $registry,
        array $data)
    {
        $this->registry = $registry;
        parent::__construct($context, $data);

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml\Mapping_Pac';
        $this->_blockGroup = 'Epicor_Dealerconnect';
        $this->_mode = 'edit';
        
        $this->removeButton('save');
        $this->removeButton('delete');
        $this->removeButton('reset');
       
    }

    public function getHeaderText()
    {
        return __('View Pac Mapping');
    }
}
