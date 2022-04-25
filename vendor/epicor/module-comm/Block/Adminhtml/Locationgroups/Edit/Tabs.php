<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Locationgroups\Edit;


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
        $this->setTitle('Group');
    }

    protected function _beforeToHtml()
    {
        $group = $this->registry->registry('group');
        /* @var $location Epicor_Comm_Model_Location */

        $detailsBlock = $this->getLayout()->createBlock('\Epicor\Comm\Block\Adminhtml\Locationgroups\Edit\Tab\Details');

        $this->addTab('form_details', array(
            'label' => 'Details',
            'title' => 'Details',
            'content' => $detailsBlock->initForm()->toHtml(),
        ));

        if ($group->getId()) {
            $this->addTab('grouplocations', array(
                'label' => 'Locations',
                'title' => 'Locations',
                'url' => $this->getUrl('*/*/grouplocations', array('id' => $group->getId(), '_current' => true)),
                'class' => 'ajax'
            ));
        }

        return parent::_beforeToHtml();
    }

}
