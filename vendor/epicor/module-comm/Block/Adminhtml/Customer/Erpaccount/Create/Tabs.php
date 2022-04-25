<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Create;


class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

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
        $this->setDestElementId('edit_form'); // this should be same as the form id define above
        $this->setTitle('Erp Account');
    }

    protected function _beforeToHtml()
    {
        $detailsBlock = $this->getLayout()->createBlock('\Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Create\Tab\Erpinfo');

        $this->addTab('form_details', array(
            'label' => 'Details',
            'title' => 'Details',
            'content' => $detailsBlock->toHtml(),
        ));

        $addressBlock = $this->getLayout()->createBlock('\Epicor\Comm\Block\Adminhtml\Customer\Erpaccount\Create\Tab\Address');

        $this->addTab('form_address', array(
            'label' => 'Addresses',
            'title' => 'Addresses',
            'content' => $addressBlock->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}
