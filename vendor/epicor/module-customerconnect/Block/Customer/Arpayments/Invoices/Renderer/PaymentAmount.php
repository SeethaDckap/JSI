<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Renderer;


class PaymentAmount extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;
    
    
    protected $arpaymentsHelper;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;       

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        array $data = []
    ) {
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $jsonEncoder,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $disableClass ='';
        $id = $row->getId(); 
        $termamount = $row->getTermBalance();
        $subtraction = "0.00";
        $style="";
        $disable = false;
        $helper = $this->arpaymentsHelper;
        if($termamount) {            
            $formatsNumber =  $helper->formatPriceWithoutCode($termamount);
            $outStandingAmount = str_replace(",", "", $formatsNumber);
        } else {
            $outStandingAmount = "0.00";
        }        
        $allowInvoiceEdit=$this->scopeConfig->getValue('customerconnect_enabled_messages/CAAP_request/is_invoice_edit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($outStandingAmount <= 0 || !$allowInvoiceEdit) {
            $disable = 'disabled=disabled';
            $disableClass ='disable_check_arpayment';
        }        
        

        $currencySymbol =$helper->getCurrencySymbol();
        $allowInvoiceEdit =$helper->getIsInVoiceEditSupported();
        $style="";
        if(!$allowInvoiceEdit) {
            $style="display:none;";
        }          
        if($row->getSelectArpayments() !="Totals") {
            if (!empty($id)) {
                $html .='<input type="hidden" name="settlement_discount" value="'.$subtraction.'" id="settlement_discount_' . $id . '" class="settlement_discount"/>';
                $html .= '<input type="hidden" name="aroutstanding_value[]" value="'.$outStandingAmount.'" id="aroutstanding_value_' . $id . '" class="aroutstanding_value" style="'.$style.'"/>';
                $html .= '<input style="width:108px;'.$style.'" onfocus="arPaymentsJs.checkOnFocus(this)"  onblur="arPaymentsJs.checkArRowTotal(this,event);" type="text" '.$disable.'  name="arpayment_amount[]" value="0" id="arpayment_amount_' . $id . '" class="arpayment_amount '.$disableClass.'"/>';
                $html .= '<input type="hidden" name="ar_remaining_value[]" value="'.trim($outStandingAmount).'" id="ar_remaining_value_' . $id . '" class="ar_remaining_value" style="'.$style.'"/>';
                $html .='<p><span id="balance_ar">Balance:<span class="price">'.$currencySymbol.'<span class="balance_ar" id="balance_ar_'.$id.'">'.$outStandingAmount.'</span></span></p>';
            } 
        } else {
            $html .='<div style=" margin-left: -79px; margin-top: 0px;">';
            $html .= "<div>Total Payment :".$currencySymbol."<span class='paymentamount_arpay'>0.00</span></div>";
            $html .='<button id="makearpayment" title="Make Payment" type="button" class="scalable task" onclick="arPaymentsJs.proceedToPreview()" style=""><span><span><span>Make Payment</span></span>
                                </span>
                            </button>';
            $html .='</div>';            
        }
        $html .='<input type="hidden" name="recalcualte_artotals" id="recalcualte_artotals" class="recalcualte_artotals" onclick="calculateOnSearchReset()" />';
        $html .=""; 
        return $html;
    }

}

?>