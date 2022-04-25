<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Listing\Renderer;

/**
 * Contract Delivery Address Reorder link grid renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class DeliveryAddress extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {

    /**
     * @var \Epicor\Common\Helper\Xml
     */
    protected $commonXmlHelper;

    public function __construct(
    \Epicor\Common\Helper\Xml $commonXmlHelper
    ) {
        $this->commonXmlHelper = $commonXmlHelper;
    }

    public function render(\Magento\Framework\DataObject $row) {

        $html = '';

        $id = $row->getId();
        if (!empty($id)) {
            $addresses = $this->commonXmlHelper->varienToArray($row->getDeliveryAddressesDeliveryAddress());
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
