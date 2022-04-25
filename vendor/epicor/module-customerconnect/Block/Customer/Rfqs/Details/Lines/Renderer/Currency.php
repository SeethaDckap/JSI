<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer;


/**
 * RFQ line currency column renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Currency extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->commMessagingHelper = $commMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        $rfq = $this->registry->registry('customer_connect_rfq_details');

        $helper = $this->commMessagingHelper;

        $index = $this->getColumn()->getIndex();
        $currency = $helper->getCurrencyMapping($rfq->getCurrencyCode(), \Epicor\Comm\Helper\Messaging::ERP_TO_MAGENTO);
        $price = $row->getData($index);
        $disIndex = $index === 'misc_line_total' ? 'line_value' : $index;
        $html = '<input type="hidden" name="lines[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $row->getData($index) . '" class="lines_' . $disIndex . '"/>';
        if ($price == 'TBC' || $price == '') {
            if($index === 'miscellaneous_charges_total') {
                $html .= '<span class="lines_' . $disIndex . '_display">' . $helper->formatPrice(0, true, $currency) . '</span>';
            }else{
                $html .= '<span class="lines_' . $disIndex . '_display">' . $price . '</span>';
            }
        } else {
            $html .= '<span class="lines_' . $disIndex . '_display">' . $helper->formatPrice($price, true, $currency) . '</span>';
        }

        return $html;
    }

}
