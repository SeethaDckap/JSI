<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Dealerconnect\Block\Claims\Details\Quotes\Renderer;


/**
 * Line reject checkbox display
 *
 * @author Gareth.James
 */
class Reject extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->customerconnectMessagingHelper;

        $status = $helper->getErpquoteStatusDescription($row->getQuoteStatus(), '', 'state');

        $disabled = '';
        $isDisabled = 0;

        if (!$this->registry->registry('rfqs_editable') || $status != \Epicor\Customerconnect\Model\Config\Source\Quotestatus::QUOTE_STATUS_AWAITING
        ) {
            $disabled = 'disabled="disabled"';
            $isDisabled = 1;
        }

        $html = '<input type="checkbox" name="rejected[]" value="' . $row->getId() . '" id="rfq_reject_' . $row->getId() . '" class="rfq_reject" ' . $disabled . '/>'
        . '<input type="hidden" name="rfq[' . $row->getId() . '][checkbox]" class="rfq_reject_checkbox" value="' . $isDisabled . '"/>';

        return $html;
    }

}
