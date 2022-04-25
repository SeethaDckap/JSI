<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Arpayments\Invoices\Renderer;


class DeliveryAddress extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
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

        $id = $row->getId();
        if (!empty($id)) {
            $helper = $this->arpaymentsHelper;
            $addresses = $helper->varienToArray($row->getDeliveryAddress());
            /* @var $helper Epicor_Common_Helper_Xml */
            // this removes fields that are not required from the address array (if only a single address, it is a string and all fields are required) 
            if (is_array($addresses)) {
                unset($addresses[0]['address_code']);
                unset($addresses[0]['purchase_order_number']);
                unset($addresses[0]['name']);
                unset($addresses[0]['telephone_number']);
                unset($addresses[0]['fax_number']);
                unset($addresses[0]['email_address']);
                $html = implode(',', $addresses[0]);
            } else {
                $html = $addresses;
            }
        }

        return $html;
    }

}

?>