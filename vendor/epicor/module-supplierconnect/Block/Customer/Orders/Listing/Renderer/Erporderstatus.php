<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Orders\Listing\Renderer;


/**
 * Invoice status display, converts an erp invoice status code to mapped invoice status
 *
 * @author Pradeep.Kumar
 */
class Erporderstatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $helper = $this->supplierconnectMessagingHelper;

        $index = $this->getColumn()->getIndex();
        return $helper->getErporderStatusDescription($row->getData($index));
    }

}
