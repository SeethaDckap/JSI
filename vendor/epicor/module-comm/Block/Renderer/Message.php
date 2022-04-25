<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Renderer;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Type
 *
 * @author Paul.Ketelle
 */
class Message extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }



    public function render(\Magento\Framework\DataObject $row)
    {
        $messageType = $row->getData($this->getColumn()->getIndex());
        $helper = $this->commMessagingHelper;
        /* @var $helper Epicor_Comm_Helper_Messaging */
        return $helper->getMessageType($messageType);
    }

}
