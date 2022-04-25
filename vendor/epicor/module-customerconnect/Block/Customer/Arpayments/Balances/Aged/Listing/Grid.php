<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Balances\Aged\Listing;


/**
 * Customer Period balances list Grid config
 */
class Grid extends \Epicor\Customerconnect\Block\Customer\Arpayments\Balances\Grid
{

    protected $arpaymentsHelper;    
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;    
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Epicor\Common\Model\Message\CollectionFactory $commonMessageCollectionFactory,
        \Epicor\Common\Helper\Data $commonHelper,
        \Magento\Framework\Url\Helper\Data $frameworkHelperDataHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Epicor\Customerconnect\Model\ArPayment\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $backendHelper,
            $commonMessageCollectionFactory,
            $commonHelper,
            $frameworkHelperDataHelper,
            $customerconnectHelper,
            $registry,
            $dataObjectFactory,
            $arpaymentsHelper,
            $data
        );
        $this->arpaymentsHelper = $arpaymentsHelper;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $context->getScopeConfig();
        $this->setId('customer_arpayments_agedbalances_list');
        $this->setSaveParametersInSession(true);
        $this->setMessageBase('customerconnect');
        $this->setExportTypeCsv(false);
        $this->setExportTypeXml(false);
        $this->setMessageType('caps');
        $this->setRequestMessageBody(true);
        $this->setDataSubset('invoices/invoice');
        $this->setFilterVisibility(true);
        $this->setPagerVisibility(false);
        $this->setCacheDisabled(true);
        $this->setShowAll(true);
        //When the user lands on the AR Payments Page for the first time, then we are getting the values from registry
        //because we are using two grids in this page and we need to send 1 message to ERP
        $details = $this->registry->registry('customer_connect_arpayments_details');
        if ($details) {
            $balanceInfo = $this->processBalances($details[0]->getVarienDataArrayFromPath('account/aged_balances/aged_balance'));
            $this->setCustomColumns($balanceInfo['columns']);
            $this->setCustomData($balanceInfo['balances']);
        } else {
            $this->setCustomColumns(array());
            $this->setCustomData(array());
        }
    }
    
    
    protected function _prepareLayout()
    {
        $this->setChild('add_agedbutton', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(array(
                    'label' => __('Clear Aged Filter'),
                    'onclick' => 'arPaymentsJs.restClearAgedFilter()',
                    'class' => 'action-secondary'
                ))
        );        
        return parent::_prepareLayout();
    }    
    
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_agedbutton');
    }

    public function getMainButtonsHtml()
    {
        $html = $this->getAddButtonHtml();
        $html .= '';
        return $html;
    }      
    
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $checkAddressId = $this->checkoutSession->getQuote()->getBillingAddress()->getId();
        $getName = $this->checkoutSession->getQuote()->getBillingAddress()->getFirstName();
        if($checkAddressId && $getName) {
            $allItems = $this->checkoutSession->getQuote()->getBillingAddress();
            $addressHtml = $allItems->format('html');
        }else {
            $allItems ='';
            $addressHtml ='';
        }
        $quoteAddress = $addressHtml;
        if($quoteAddress){
            $addressStyle='display:block;';
            $valuequoteAddress=true;
        }else{
            $addressStyle='display:none;';
            $valuequoteAddress=false;
        }
        $helper = $this->arpaymentsHelper;
        $currency_symbol =$helper->getCurrencySymbol();        
        /* @var $checkCaapActive Epicor_Customerconnect_Model_Arpayments */        
        $style="";
        $checkCaapActive=true;
        if(!$checkCaapActive) {
            $style="display:none;";
        }
        $billingAddress=null;
        $billingReadAllowed = $this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_ar_payment_billing_read");
        $billingEditAllowed = $this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_ar_payment_billing_edit");
        $agedDebtsAllowed = $this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_ar_payment_aged_debts_read");
        //If the AR Payment Quotes have Address then only display the $billing address
        if($valuequoteAddress && ($billingReadAllowed || $billingEditAllowed)){
          //Billing Address In the Ar Payments Landing Page
         $billingAddress='<div class="address_block" id="address_block"><div class="address_label">'
                         . '<label for="allocate_amount"><h3 class="billingaddressheading">Card Holders Billing Address:</h3></label></div>'
                         . '<div id="landing_address_content" class="address_content">';
         if ($billingReadAllowed) {
             $billingAddress.= $addressHtml . '<br>';
         }
         if ($billingEditAllowed) {
             $billingAddress .= '<span class="change_address" onclick="arPaymentsJs.addOrUpdateAddress(1)">Change Address</span>';
         }
            $billingAddress .= '</div></div>';
        }
        //IF CAAP Message was enabled then only Enable the Edit Option
        $allowInvoiceEdit=$helper->getIsInVoiceEditSupported();
        $totalAmountToApply = null;
        $disableApplyButton='<div>
                    <p>
                        <br>
                        <button id="allocatebutton" title="Search" type="button" class="scalable task" onclick="arPaymentsJs.calculateArSum()" style="">
                        <span><span><span>Apply Payment</span></span>
                         </span>
                        </button>
                    </p>
                </div>';
         $totalAmountToApply = '<fieldset style="border:0px;">
                    <label for="allocate_amount">Total Amount to Apply</label>
                    ' . $currency_symbol . '<input onfocus="arPaymentsJs.checkOnFocus(this)" onblur="arPaymentsJs.blurAllocateAmount()" name="allocate_amount" style="width:136px" maxlength="8"  id="allocate_amount" class="input-text no-changes allocate_amount" type="text">
                    <input  name="allocate_amount_original" id="allocate_amount_original"  type="hidden" value="">
                    <input  name="canUpdateByInvoice" id="canUpdateByInvoice"  type="hidden" value="0">
                    <input name="payment_on_account" id="payment_on_account"   type="checkbox" ><label for="payment_on_account" style="padding-left:5px">Payment On Account</label>
                </fieldset>';

        if ($agedDebtsAllowed) {
            $html = '<div class="ar_payment_block">
                <div class="payment_block" id="payment_block" style="'.$style.'">
                    <p>'.
                        $totalAmountToApply
                        .'<fieldset style="clear:both;padding-top:10px;border:0px;" id="arpayment_left">
                            <label for="amount_left_ar" id="amount_left_ar_label">Amount Left</label>
                             <span style="padding-left:7px;">'.$currency_symbol.'</span><span class="amount_left_ar">0.00</span>
                             <input type="hidden" name="amount_left_ar" value="" id="amount_left_ar" class="amount_left_hiddenar"/>
                        </fieldset>                        
                    </p>'.$disableApplyButton.'</div><input type="hidden" id="arpayment_address_value" value="'.$valuequoteAddress.'" />'.$billingAddress.'</div>
                    <input type="hidden" value="'.$this->getUrl().'" id="baseUrl"/>
                <br>'. $html;

        } else {
            $html = '<div class="ar_payment_block"><input type="hidden" id="arpayment_address_value" value="'.$valuequoteAddress.'" />'.$billingAddress.'</div>
                    <input type="hidden" value="'.$this->getUrl().'" id="baseUrl"/>
                <br>';
        }
        return $html;
    }      

}