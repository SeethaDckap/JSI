<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Invoices\Listing\Renderer;


/**
 * Invoice status "Open" display, shows Yes / No based on status value
 *
 * @author Gareth.James
 */
class Invoicestatus extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        array $data = []
    ) {
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {

        $helper = $this->customerconnectMessagingHelper;

        $index = $this->getColumn()->getIndex();
        return $helper->getInvoiceStatusDescription($row->getData($index));
    }
}
