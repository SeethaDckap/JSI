<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Comm\Block\Adminhtml\Sales\Returns\View;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Tabs extends \Magento\Backend\Block\Widget\Tabs {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Json\EncoderInterface $jsonEncoder, \Magento\Backend\Model\Auth\Session $authSession, \Magento\Framework\Registry $registry, array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
                $context, $jsonEncoder, $authSession, $data
        );
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form'); // this should be same as the form id define above
        $this->setTitle('Return');
    }

    protected function _beforeToHtml() {
        $return = $this->registry->registry('return');

        $detailsBlock = $this->getLayout()->createBlock('\Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab\Details');

        $this->addTab('form_details', array(
            'label' => 'Details',
            'title' => 'Details',
            'content' => $detailsBlock->toHtml(),
        ));

        $statusBlock = $this->getLayout()->createBlock('\Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab\Status');

        $this->addTab('form_status', array(
            'label' => 'Status',
            'title' => 'Status',
            'content' => $statusBlock->toHtml(),
        ));

        $logBlock = $this->getLayout()->createBlock('\Epicor\Comm\Block\Adminhtml\Sales\Returns\View\Tab\Log');

        $this->addTab('form_log', array(
            'label' => 'Message Log',
            'title' => 'Message Log',
            'url' => $this->getUrl('*/*/logsgrid', array('id' => $return->getId(), '_current' => true)),
            'content' => $logBlock->toHtml(),
            'class' => 'ajax'
        ));

        return parent::_beforeToHtml();
    }

}
