<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Access\Admin;


class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * Initialize Tabs
     *
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $jsonEncoder,
            $authSession,
            $data
        );
        $this->setId('form_tabs');
        $this->setDestElementId('admin_form'); // this should be same as the form id define above
        $this->setTitle('Access Management Administration');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_element', array(
            'label' => 'Excluded Elements',
            'title' => 'Excluded Elements',
            'url' => $this->getUrl('*/*/excludedelements', array('_current' => true)),
            'class' => 'ajax',
        ));

        return parent::_beforeToHtml();
    }

}
