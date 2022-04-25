<?php
/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines\Renderer;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class MiscCharges extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    const FRONTEND_RESOURCE_INFORMATION_READ = 'Epicor_Customerconnect::customerconnect_account_invoices_misc';
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
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    protected $urlEncoder;

    /**
     * @var \Epicor\AccessRight\Model\Authorization
     */
    protected $_accessauthorization;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\App\Request\Http $request,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Url\DecoderInterface $urlDecoder,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    )
    {
        $this->commMessagingHelper = $commMessagingHelper;
        $this->request = $request;
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->encryptor = $encryptor;
        $this->urlDecoder = $urlDecoder;
        $this->urlEncoder = $urlEncoder;
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
        return $html;
    }

    public function canShowMisc()
    {
        $showMiscCharges = $this->customerconnectHelper->showMiscCharges();
        $isMiscAllowed = $this->_accessauthorization->isAllowed(static::FRONTEND_RESOURCE_INFORMATION_READ);
        return $showMiscCharges && $isMiscAllowed;
    }


}
