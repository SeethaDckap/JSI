<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Invoices\Details\Lines\Renderer;


class Packingslip extends \Epicor\AccessRight\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Epicor\Common\Helper\Access
     */
    protected $commonAccessHelper;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Common\Helper\Access $commonAccessHelper,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        array $data = []
    )
    {
        $this->commonAccessHelper = $commonAccessHelper;
        $this->customerconnectHelper = $customerconnectHelper;
        $this->registry = $registry;
        $this->urlEncoder = $urlEncoder;
        $this->encryptor = $encryptor;
        parent::__construct(
            $context,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {

        $html = '';
        $id = $row->getPackingSlip();
        if ($this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_shipments_details")) {
            $helper = $this->customerconnectHelper;
            $erp_account_number = $helper->getErpAccountNumber();
            $invoice = $this->registry->registry('customer_connect_invoices_details');
            if ($invoice) {
                $packing_slip_requested = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $row->getPackingSlip() . ']:[' . $invoice->getOurOrderNumber()));

                $new_url = $this->getUrl('customerconnect/shipments/details', array('shipment' => $packing_slip_requested, 'back' => $this->urlEncoder->encode($this->getUrl('*/*/*', $this->getRequest()->getParams()))));

                if (!empty($id)) {
                    $html = '<a href="' . $new_url . '">' . $id . '</a>';
                }
            }
        } else {
            if (!empty($id)) {
                $html = $id;
            }
        }

        return $html;
    }

}
