<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Renderer;


class Dispute extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    
    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;
    
    public function __construct(\Magento\Backend\Block\Context $context, 
                                \Magento\Framework\Json\EncoderInterface $jsonEncoder, 
                                \Epicor\Customerconnect\Helper\Data $customerconnectHelper, 
                                array $data = [])
    {
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct($context, $jsonEncoder, $data);
    }
    
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $outStandingAmount = $row->getOutstandingValue();
        $disableClass      = '';
        $disable           = '';
        $html              = '';
        if ($outStandingAmount <= 0) {
            $disable      = 'disabled=disabled';
            $disableClass = 'disable_check_arpayment';
        }
        if ($row->getSelectArpayments() != "Totals") {
            $jqueryFunction     = "arPaymentsJs.disputeClick('" . $row->getId() . "')";
            $jqueryPlusFunction = "arPaymentsJs.disputePlusClick('" . $row->getId() . "')";
            $html .= '<input onclick="' . $jqueryFunction . '" type="checkbox" ' . $disable . ' name="dispute_invoice[]" value="' . $row->getId() . '" id="dispute_invoices_' . $row->getId() . '" class="dispute_invoices ' . $disableClass . '"/>';
            $html .= '<div>';
            $html .= '<div id="expand_row_' . $row->getId() . '" onclick="' . $jqueryPlusFunction . '" class="expand-row"><span  class="plus-minus" style="font-size: 27px;" id="' . $row->getId() . '">+</span></div>' . '<textarea data-id="' . $row->getId() . '"   name="dispute_invoices_comments[]" '
                  . 'style="display:none;resize:both;width: 208px;height: 180px;" class="dispute_invoices_comments" id="dispute_invoices_comments_' . $row->getId() . '"></textarea>' . '</div>';
            $html .='<input type="hidden"  name="dispute_invoices_serializecomments[]" class="dispute_invoices_serializecomments" id="dispute_invoices_serializecomments_' . $row->getId() . '"/>';
            $html .='<input type="hidden"  name="dispute_invoice_serialize[]" class="dispute_invoice_serialize" id="dispute_invoice_serialize_' . $row->getId() . '"/>';
        } else {
            
        }
        return $html;
    }
    
}

?>