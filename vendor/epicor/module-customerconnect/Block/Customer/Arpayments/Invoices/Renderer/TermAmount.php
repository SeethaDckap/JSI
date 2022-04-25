<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Renderer;


class TermAmount extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
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
        $outStandingAmount='';
        $id = $row->getId(); 
        $termamount = $row->getTermBalance();
        $style="";
        $disable = false;
        $helper = $this->arpaymentsHelper;
        $currencySymbol =$helper->getCurrencySymbol();        
        if($row->getSelectArpayments() !="Totals") {
            if($termamount) {            
                $outStandingAmount = $termamount;
            } else {
                $outStandingAmount = "0.00";
            }
        } else {
            $outStandingAmount='';
        }
        
        if($outStandingAmount) {
            $outStandingAmount = $helper->formatPrices($outStandingAmount);
        }
        
        
        return $outStandingAmount;
    }

}

?>