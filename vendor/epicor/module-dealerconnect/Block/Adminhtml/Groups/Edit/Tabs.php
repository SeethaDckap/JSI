<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Adminhtml\Groups\Edit;


/**
 * Dealer Groups edit tabs
 *
 * @category   Epicor
 * @package    Epicor_Dealerconnect
 * @author     Epicor Websales Team
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Epicor\Dealerconnect\Model\Dealergroups
     */
    private $_dealerGrp;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Dealerconnect\Model\DealergroupsFactory
     */
    protected $dealerGroupModelFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Epicor\Dealerconnect\Model\DealergroupsFactory $dealerGroupModelFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->dealerGroupModelFactory = $dealerGroupModelFactory;
        parent::__construct(
            $context,
            $jsonEncoder,
            $authSession,
            $data
        );
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle('Dealer Group');
    }

    protected function _beforeToHtml()
    {
        $dealerGrp = $this->getDealerGrp();
        /* @var $dealerGrp Epicor_Dealerconnect_Model_Dealergroups */


        $this->addTab('details', array(
            'label' => 'Details',
            'title' => 'Details',
            'content' => $this->getLayout()->createBlock('\Epicor\Dealerconnect\Block\Adminhtml\Groups\Edit\Tab\Details')->toHtml(),
        ));

        $this->addTab('erpaccounts', array(
            'label' => 'Dealer Accounts',
            'title' => 'Dealer Accounts',
            'url' => $this->getUrl('*/*/erpaccounts', array('id' => !is_null($dealerGrp) ? $dealerGrp->getId() : '', '_current' => true)),
            'class' => 'ajax',
        ));

        return parent::_beforeToHtml();
    }

    /**
     * Gets the current Group
     *
     * @return \Epicor\Dealerconnect\Model\Dealergroups
     */
    public function getDealerGrp()
    {
        if (!isset($this->_dealerGrp)) {
            $regDealer = $this->registry->registry('dealergrp');

            if ($regDealer && $regDealer->getId()) {
                $this->_dealerGrp = $this->registry->registry('dealergrp');
            } else {
                $this->_dealerGrp = $this->dealerGroupModelFactory->create()->load($this->getRequest()->getParam('id'));
            }
        }
        return $this->_dealerGrp;

    }

}
