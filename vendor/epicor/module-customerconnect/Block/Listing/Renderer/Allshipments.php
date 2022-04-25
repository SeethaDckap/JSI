<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Listing\Renderer;

use Epicor\Comm\Helper\Data;
use Epicor\Common\Block\Renderer\Encodedlinkabstract;
use Epicor\Common\Helper\Access;
use Epicor\Customerconnect\Helper\Data as CustomerconnecthelperData;
use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Registry;
use Magento\Framework\Url\EncoderInterface;

/**
 * Order link display
 *
 * @author Sean Flynn
 */
class Allshipments extends Encodedlinkabstract
{
    /**
     * @var CustomerconnecthelperData
     */
    protected $customerconnectHelper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Allshipments constructor.
     * @param Context $context
     * @param Access $commonAccessHelper
     * @param Data $commHelper
     * @param EncoderInterface $urlEncoder
     * @param EncryptorInterface $encryptor
     * @param CustomerconnecthelperData $customerconnectHelper
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Access $commonAccessHelper,
        Data $commHelper,
        EncoderInterface $urlEncoder,
        EncryptorInterface $encryptor,
        CustomerconnecthelperData $customerconnectHelper,
        Registry $registry,
        array $data = []
    ) {
        $this->customerconnectHelper = $customerconnectHelper;
        $this->registry = $registry;
        parent::__construct(
            $context,
            $commonAccessHelper,
            $commHelper,
            $urlEncoder,
            $encryptor,
            $data
        );
    }

    /**
     * @param $url
     * @param $id
     * @return string
     */
    private function getPackingSlipLink($url, $id)
    {
        if(!$this->isExportAction()){
            return '<a href="' . $url . '">' . $id . '</a>';
        }

        return $id;
    }

    /**
     * @param DataObject $row
     * @return array|mixed|string
     */
    public function render(DataObject $row)
    {
        $html = '';
        $id = $row->getPackingSlip();
        if ($this->_isAccessAllowed("Epicor_Customerconnect::customerconnect_account_shipments_details")) {
            $helper = $this->customerconnectHelper;
            $erp_account_number = $helper->getErpAccountNumber();
            $packing_slip_requested = $this->urlEncoder->encode($this->encryptor->encrypt($erp_account_number . ']:[' . $row->getPackingSlip() . ']:[ordernumberempty'));

            $new_url = $this->getUrl('customerconnect/shipments/details', array('shipment' => $packing_slip_requested));

            if (!empty($id)) {
                $html = $this->getPackingSlipLink($new_url, $id);
            }
        } else {
            if (!empty($id)) {
                $html = $id;
            }
        }

        return $html;
    }

}
