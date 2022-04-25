<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Quickstart\Edit;


/**
 * Created by PhpStorm.
 * User: lguerra
 * Date: 12/2/14
 * Time: 2:45 PM
 */
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
        $this->setTitle(__('Quick Start'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label' => __('General'),
            'title' => __('General'),
            'content' => $this->getLayout()->createBlock('\Epicor\Common\Block\Adminhtml\Quickstart\Edit\Tab\General\Form')->toHtml()
        ));

        $this->addTab('customer', array(
            'label' => __('Customer Settings'),
            'title' => __('Customer Settings'),
            'url' => $this->getUrl('*/*/customersettings'),
            'class' => 'ajax',
        ));

        $this->addTab('products_configurator', array(
            'label' => __('Products/Configurator Settings'),
            'title' => __('Products/Configurator Settings'),
            'url' => $this->getUrl('*/*/productsconfiguratorsettings'),
            'class' => 'ajax',
            //'content'   => $this->getLayout()->createBlock('epicor_common/adminhtml_quickstart_edit_tab_productsconfigurator_form')->toHtml()
        ));

        $this->addTab('checkout', array(
            'label' => __('Checkout Settings'),
            'title' => __('Checkout Settings'),
            'url' => $this->getUrl('*/*/checkoutsettings'),
            'class' => 'ajax',
        ));

        $this->addTab('b2b', array(
            'label' => __('B2B Settings'),
            'title' => __('B2B Settings'),
            'url' => $this->getUrl('*/*/b2bsettings'),
            'class' => 'ajax',
        ));

        return parent::_beforeToHtml();
    }

}
