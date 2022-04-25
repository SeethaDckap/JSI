<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Orders\Details\Parts\Renderer;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Shipping
 *
 * @author Paul.Ketelle
 */
class Shipping extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_orders_misc';
    const FRONTEND_RESOURCE_INFORMATION_READ_DEALER = 'Dealer_Connect::dealer_orders_misc';

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Epicor\Dealerconnect\Helper\Data
     */
    protected $dealerHelper;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    protected $urlEncoder;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Epicor\Dealerconnect\Helper\Data $dealerHelper,
        array $data = []
    ) {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->request = $request;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->encryptor = $encryptor;
        $this->urlDecoder = $urlDecoder;
        $this->urlEncoder = $urlEncoder;
        $this->dealerHelper = $dealerHelper;
        $this->_accessauthorization = $context->getAccessAuthorization();
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $helper = $this->commMessagingHelper;
        $html = '';

        $shipments = ($row->getShipments()) ? $row->getShipments()->getasarrayShipment() : array();
        $miscellaneousCharges = ($row->getMiscellaneousCharges()) ? $row->getMiscellaneousCharges()->getasarrayMiscellaneousLine() : array();
        $showMiscCharges = $this->canShowMisc();
        $defaultMiscView = $this->customerconnectHelper->checkCusMiscView();
        $defDisplay = 'display:none';
        if ($defaultMiscView) {
            $defDisplay = '';
        }

        if (count($miscellaneousCharges) > 0 && $showMiscCharges) {

            $html .= '<tr id="row-misc-' . $row->getUniqueId() . '" style=' . $defDisplay . '><td colspan="12" class="">
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
                    <td>' . ($misc['type'] === 'A' ? '' : $misc['percentage']) . '</td>
                    <td>' . $helper->getCurrencyConvertedAmount($misc['line_value'], $currencyCode) . '</td>
                  </tr>
                    ';
            }
            $html .= '</tbody></table>';
        }

        if (count($shipments) > 0) {
            $html .= '</td></tr><tr id="row-shipments-' . $row->getUniqueId() . '" style='.$defDisplay.'><td colspan="8" class="shipping-row">
            <table class="expand-table">
                <thead>
                    <tr class="headings">
                        <th>' . __('Status') . '</th>
                        <th>' . __('Date') . '</th>
                        <th>' . __('Quantity') . '</th>
                        <th>' . __('Ship Via') . '</th>
                        <th>' . __('Pack Slip') . '</th>
                    </tr>
                </thead>
                <tbody>
            ';
            // pick out account no from passed parm
            $order_requested = explode(']:[', $this->encryptor->decrypt($this->urlDecoder->decode($this->request->getParam('order'))));

            $accessHelper = $this->commonAccessHelper;
            $hasAccess = $accessHelper->customerHasAccess('Epicor_Customerconnect', 'Shipments', 'details', '', 'Access');

            foreach ($shipments as $shipment) {

                $helper = $this->customerconnectHelper;
                $erp_account_number = $helper->getErpAccountNumber();

                if (!empty($shipment['shipment_date'])) {
                    //M1 > M2 Translation Begin (Rule 32)
                    //$shipmentDate = $helper->getLocalDate($shipment['shipment_date'], \Epicor\Common\Helper\Data::DAY_FORMAT_MEDIUM, true);
                    $shipmentDate = $helper->getLocalDate($shipment['shipment_date'], \IntlDateFormatter::MEDIUM, true);
                    //M1 > M2 Translation End
                } else {
                    $shipmentDate = __('N/A');
                }

                if ($hasAccess) {
                    $packing_slip_requested = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $shipment['packing_slip'] . ']:[' . $order_requested[1]));
                    $new_url = $this->getUrl('customerconnect/shipments/details', array('shipment' => $packing_slip_requested, 'back' => $this->urlEncoder->encode($this->getUrl('*/*/*', $this->getRequest()->getParams()))));
                    $packSlipLink = '<a href="' . $new_url . '">' . $shipment['packing_slip'] . '</a>';
                } else {
                    $packSlipLink = $shipment['packing_slip'];
                }

                $html .= '
                  <tr>
                    <td>' . $shipment['shipment_status'] . '</td>
                    <td>' . $shipmentDate . '</td>
                    <td>' . floatval($shipment['quantity']) . '</td>
                    <td>' . $shipment['delivery_method'] . '</td>
                    <td>' . $packSlipLink . '</td>
                  </tr>
                    ';
            }
            $html .= '</tbody></table>';
        }
        return $html;
    }

    public function canShowMisc()
    {
        $showMiscCharges = $this->customerconnectHelper->showMiscCharges();
        $isDealer = $this->dealerHelper->isDealerPortal();
        $code = $isDealer ? static::FRONTEND_RESOURCE_INFORMATION_READ_DEALER: static::FRONTEND_RESOURCE_INFORMATION_READ;
        $isMiscAllowed = $this->_accessauthorization->isAllowed($code);
        return $showMiscCharges && $isMiscAllowed;

    }

}
