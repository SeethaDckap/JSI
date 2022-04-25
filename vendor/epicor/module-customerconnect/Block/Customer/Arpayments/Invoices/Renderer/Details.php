<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Renderer;


class Details extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    ) {
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct(
            $context,
            $jsonEncoder,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        $id = $row->getId();
        if (!empty($id)) {
            $helper = $this->customerconnectHelper;
            /* @var $helper Epicor_Customerconnect_Helper_Data */
            $erpAccountNumber = $helper->getErpAccountNumber();
            $invoice = $helper->getEncodeOrderDetailsUrl($erpAccountNumber,$row->getId());
            $jsonData = $this->showDetails($row);
            $strId = preg_replace('/\s+/', '', $row->getId());
            $html = '<a id="details_'.$id.'"href="#" onclick="arPaymentsJs.detailspopup(\'' . $invoice . '\',\'' . $strId . '\'); return false;">' . __('Details') . '</a>';
            $html .='<input type="hidden" class="arpaymentjson" name="arpaymentjson[]" id="arpaymentjson_'.$id.'" value=\''.$jsonData.'\'>';
        }
        return $html;
    }
    
    
    public function showDetails($row) {
       $invoiceDate = $this->processDate($row->getInvoiceDate());
       $dueDate = $this->processDate($row->getDueDate());
       $data = array();
       $data['invoiceNo'] = $row->getInvoiceNumber();
       $data['invoiceDate'] = ($invoiceDate) ? $row->getInvoiceDate() : "N/A";
       $data['dueDate']    = ($dueDate) ? $row->getDueDate() : "N/A";
       $data['invoiceAmount'] = $row->getOriginalValue();
       $data['invoiceBalance'] = $row->getOutstandingValue();
       $data['paidAmount']   = $row->getPaymentValue();
       $data['termBalance'] = $row->getTermBalance();
       $data['agedPeriodNumber'] = $row->getAgedPeriodNumber();
       /*$data['deliveryAddressName'] = $row->getDeliveryAddressName();
       $data['invoiceNo'] = $row->getDeliveryAddressAddress1();
       $data['address1'] = $row->getDeliveryAddressAddress1();
       $data['address2'] = $row->getDeliveryAddressAddress2();
       $data['address3'] = $row->getDeliveryAddressAddress3();
       $data['city'] = $row->getDeliveryAddressCity();
       $data['county'] = $row->getDeliveryAddressCounty();
       $data['country'] = $row->getDeliveryAddressCountry();
       $data['postcode'] = $row->getDeliveryAddressPostcode();*/
       $data['deliveryAddress'] = $row->getDeliveryAddress();
       return json_encode($data);
    }

    /**
     * 
     * Get processed date
     * @param string
     * @return string
     */
    public function processDate($rawDate=NULL)
    {
        if ($rawDate) {
            $timePart = substr($rawDate, strpos($rawDate, "T") + 1);
            if (strpos($timePart, "00:00:00") !== false) {
                $processedDate = $this->customerconnectHelper->getLocalDate($rawDate, \IntlDateFormatter::MEDIUM, false);
            } else {
                $processedDate = $this->customerconnectHelper->getLocalDate($rawDate, \IntlDateFormatter::MEDIUM, false);
            }
        } else {
            $processedDate = '';
        }
        return $processedDate;
    }        

}

?>