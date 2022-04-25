<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Details\Lines\Renderer;


/**
 * Currency display, converts a row value to currency display
 *
 * @author Gareth.James
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Supplierconnect\Helper\Messaging
     */
    protected $supplierconnectMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Supplierconnect\Helper\Messaging $supplierconnectMessagingHelper,
        array $data = []
    ) {
        $this->supplierconnectMessagingHelper = $supplierconnectMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {

        return $this->supplierconnectMessagingHelper->getErpOrderStatusDescription($row->getLineStatus());
    }

}
