<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Renderer;


class InvoiceBalance extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;
    
    
    protected $arpaymentsHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Epicor\Customerconnect\Helper\Arpayments $arpaymentsHelper,
        array $data = []
    ) {
        $this->arpaymentsHelper = $arpaymentsHelper;
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
        $helper = $this->arpaymentsHelper;
        $currencySymbol =$helper->getCurrencySymbol();        
        $invoiceAmount = $row->getOutstandingValue();
        if($row->getSelectArpayments() !="Totals") {
            $html = '<span class="price">'.$helper->formatPrices($row->getOutstandingValue()).'</span>';
        }  else {
            $html = '<span class="price"><strong>'.$helper->formatPrices($row->getOutstandingValue()).'</strong></span>';
        } 
        return $html;
    }

}

?>