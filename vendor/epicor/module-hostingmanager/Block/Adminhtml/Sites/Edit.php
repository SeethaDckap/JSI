<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\HostingManager\Block\Adminhtml\Sites;


/**
 * Sites edit block
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->_controller = 'adminhtml\Sites';
        $this->_blockGroup = 'Epicor_HostingManager';
        $this->_mode = 'edit';

        parent::__construct(
            $context,
            $data
        );

        $this->removeButton('reset');
        $this->addButton('reset', array(
            'label' => __('Reset'),
            'onclick' => 'setLocation(\'' . $this->getResetUrl() . '\')',
            ), -1);
    }

    public function getResetUrl()
    {
        return $this->getUrl('*/*/edit', array($this->_objectId => $this->getRequest()->getParam($this->_objectId)));
    }

    public function getHeaderText()
    {
        if ($this->registry->registry('current_site')->getId()) {
            return $this->escapeHtml($this->registry->registry('current_site')->getName());
        } else {
            return __('New Site');
        }
    }

}
