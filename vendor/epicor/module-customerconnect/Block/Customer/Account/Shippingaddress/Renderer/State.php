<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Account\Shippingaddress\Renderer;


class State extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    //   protected $updateList = Array();
    public function render(\Magento\Framework\DataObject $row)
    {

//        $helper = Mage::helper('customerconnect');
//        /* @var $helper Epicor_Customerconnect_Helper_Data */
//        $countryCode = $helper->getCountryCodeForDisplay($row->getCountry());
//        $region = $helper->getRegionFromCountyName($countryCode, $row->getCounty());
//
//        return ($region) ? $region->getName() : $row->getCounty();
        return $row->getCounty();
    }

}
