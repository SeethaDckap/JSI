<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Dealerconnect\Block\Customer\Quotes\Details\Lines\Renderer;


/**
 * RFQ line attachments column renderer
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Attachments extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    const FRONTEND_RESOURCE_INFORMATION_READ_DEALER = 'Dealer_Connect::dealer_quotes_misc';
    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->commMessagingHelper;
        $html = '';
        $attachments = ($row->getAttachments()) ? $row->getAttachments()->getasarrayAttachment() : array();
        if ($this->registry->registry('rfqs_editable')) {
            $colspan = 12;
        } else {
            $colspan = 11;
        }

        $miscellaneousCharges = ($row->getMiscellaneousCharges()) ? $row->getMiscellaneousCharges()->getasarrayMiscellaneousLine() : array();
        $showMiscCharges = $this->canShowMisc();
        $defaultMiscView = $this->customerconnectHelper->checkCusMiscView();
        $defDisplay = 'display:none';
        if ($defaultMiscView) {
            $defDisplay = '';
        }
        $colspan = $showMiscCharges ? $colspan + 1 : $colspan;

        if (count($miscellaneousCharges) > 0 && $showMiscCharges) {
            $html .= '<tr class="lines_row attachment" id="row-misc-' . $row->getUniqueId() . '" style=' . $defDisplay . '><td colspan='.$colspan.' class="misc-row">
            <table class="expand-table misc-row">
                <thead>
                    <tr class="headings">
                        <th>' . __('Description') . '</th>
                        <th>' . __('Percentage') . '</th>
                        <th>' . __('Total Misc.') . '</th>
                    </tr>
                </thead>
                <tbody>
            ';

            foreach ($miscellaneousCharges as $misc) {
                $currencyCode = $helper->getCurrencyMapping($misc['currency_code'], \Epicor\Customerconnect\Helper\Data::ERP_TO_MAGENTO);
                $html .= '
                  <tr>
                    <td>' . $misc['description'] . '</td>
                    <td>' . ($misc['type'] === 'A' ? '' : $misc['percentage']). '</td>
                    <td>' . $helper->getCurrencyConvertedAmount($misc['line_value'], $currencyCode) . '</td>
                  </tr>
                    ';
            }
            $html .= '</tbody></table>';
        }

        $html .= '</td>'
            . '</tr>'
            . '<tr class="lines_row attachment" id="row-attachments-' . $row->getUniqueId() . '" style='.$defDisplay.'>'
            . '<td colspan="' . $colspan . '" class="shipping-row">';

        if ($this->registry->registry('current_rfq_row')) {
            $this->registry->unregister('current_rfq_row');
        }

        $this->registry->register('current_rfq_row', $row);

        //if (!$block = $this->getLayout()->getBlock('lines.attachments')) {
        $block = $this->getLayout()->createBlock('Epicor\Dealerconnect\Block\Customer\Quotes\Details\Lines\Attachments');
        //}

        $html .= $block->toHtml();

        return $html;
    }

    public function canShowMisc()
    {
        $showMiscCharges = $this->customerconnectHelper->showMiscCharges();
        $isMiscAllowed = $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ_DEALER);
        return $showMiscCharges && $isMiscAllowed;
    }

}
