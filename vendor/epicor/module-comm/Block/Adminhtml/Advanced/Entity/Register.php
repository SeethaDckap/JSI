<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Advanced\Entity;


class Register extends \Magento\Backend\Block\Widget\Grid\Container
{

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
         \Epicor\Comm\Helper\Context $helperContext,
        array $data = []
    )
    {
         $this->urlDecoder = $helperContext->getUrlDecoder();
        $this->_controller = 'adminhtml_advanced_entity_register';
        $this->_blockGroup = 'Epicor_Comm';
        $this->_headerText = __('Uploaded Data');

        parent::__construct(
            $context,
            $data
        );

        $this->removeButton('add');

        if ($this->getRequest()->getParam('back')) {
            $url = $this->urlDecoder->decode($this->getRequest()->getParam('back'));
            $this->addButton(
                'back', array(
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class' => 'back',
                ), -1
            );
        }
    }

}
