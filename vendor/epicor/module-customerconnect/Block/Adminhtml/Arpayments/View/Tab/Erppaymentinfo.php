<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Adminhtml\Arpayments\View\Tab;

class Erppaymentinfo extends \Magento\Framework\View\Element\Text\ListText implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'arpayments/view/tab/erppaymentinfo.phtml';
    
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('ERP Payment Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('ERP Payment Information');
    }
    
    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('adminhtml/arpayments/erppaymentinfo', ['_current' => true]);
    }
    
    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
    
    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
