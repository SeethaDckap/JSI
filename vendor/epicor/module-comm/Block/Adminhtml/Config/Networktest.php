<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Config;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Networktest
 *
 * @author David.Wylie
 */
class Networktest extends \Magento\Config\Block\System\Config\Form\Field
{
    /*
     * Set template
     */

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->backendHelper = $backendHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('epicor_comm/config/networktest.phtml');
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return $this->backendHelper->getUrl('adminhtml/epicorcomm_message_ajax/networktest');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(array(
            'id' => 'network_test_button',
            'label' => __('Test Connection'),
            'onclick' => 'javascript:check(); return false;'
        ));

        return $button->toHtml();
    }

}
