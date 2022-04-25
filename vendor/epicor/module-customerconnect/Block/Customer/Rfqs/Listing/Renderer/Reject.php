<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Listing\Renderer;


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
        $_allowedStatuses = $helper->confirmRejectQuoteStatus($status, $row);
        if (!$this->registry->registry('rfqs_editable')
            || $_allowedStatuses
        ) {
            $disabled = 'disabled="disabled"';
        }

        $html = '<input type="checkbox" name="rejected[]" value="' . $row->getId() . '" id="rfq_reject_' . $row->getId() . '" class="rfq_reject" ' . $disabled . '/>';

        return $html;
    }

}
