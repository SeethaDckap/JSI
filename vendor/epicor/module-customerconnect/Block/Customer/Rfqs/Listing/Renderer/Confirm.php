<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Listing\Renderer;


/**
 * Line comment display
 *
 * @author Gareth.James
 */
class Confirm extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Quotes\Model\QuoteFactory
     */
    protected $quotesQuoteFactory;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Quotes\Model\QuoteFactory $quotesQuoteFactory,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    ) {
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->quotesQuoteFactory = $quotesQuoteFactory;
        $this->customerconnectHelper = $customerconnectHelper;
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

        $erpAccount = $helper->getErpAccountInfo();
        $currencyCode = $erpAccount->getCurrencyCode($this->storeManager->getStore()->getBaseCurrencyCode());
        $_allowedStatuses = $helper->confirmRejectQuoteStatus($status, $row);
        if (!$this->registry->registry('rfqs_editable')
            || $_allowedStatuses
            || !$currencyCode
        ) {
            $disabled = 'disabled="disabled"';
        }

        $html = '<input type="checkbox" name="confirmed[]" value="' . $row->getId() . '" id="rfq_confirm_' . $row->getId() . '" class="rfq_confirm" ' . $disabled . '/>'
            . '<input type="hidden" name="rfq[' . $row->getId() . '][quote_number]" value="' . $row->getQuoteNumber() . '"/>'
            . '<input type="hidden" name="rfq[' . $row->getId() . '][quote_sequence]" value="' . $row->getQuoteSequence() . '"/>'
            . '<input type="hidden" name="rfq[' . $row->getId() . '][recurring_quote]" value="' . $row->getRecurringQuote() . '"/>'
            . '<input type="hidden" name="rfq[' . $row->getId() . '][amount]" id="rfq_' . $row->getId() . '_total" value="' . $row->getOriginalValue() . '"/>'
        ;

        $html .= '<p id="rfq_' . $row->getId() . '_customer_reference_box" style="display:none">';
        $html .= '<label for="rfq_' . $row->getId() . '_customer_reference">Reference:</label>';
        $html .= '<input type="text" name="rfq[' . $row->getId() . '][customer_reference]" id="rfq_' . $row->getId() . '_customer_reference" value=""/>';
        $html .= '</p>';

        $helper = $this->commonAccessHelper;

        if ($disabled == '' && $helper->customerHasAccess('Epicor_Quotes', 'Manage', 'accept', '', 'Access')) {
            $quoteNumber = $row->getQuoteNumber();

            $quote = $this->quotesQuoteFactory->create();
            $quote->load($quoteNumber, 'quote_number');

            if ($quote->getId() && $quote->isAcceptable()) {
                $helper = $this->customerconnectHelper;

                $return = $this->getUrl('quotes/manage/accept/', array('id' => $quote->getId()));
                $img = $this->getViewFileUrl('Epicor_Customerconnect::epicor/customerconnect/images/checkout_icon.gif');
                $html .= '<a href="' . $return . '" class="rfq_checkout_link">'
                    . ' <img src="' . $img . '" alt="' . __('Checkout') . '" /> '
                    . '</a>';
            }
        }

        return $html;
    }

}
