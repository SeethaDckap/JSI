<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Account\Shippingaddress\Renderer;


class Shippingaddress extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    //   protected $updateList = Array();


    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    //   protected $updateList = Array();
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Customerconnect\Helper\Data $customerconnectHelper,
        array $data = []
    )
    {
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct(
            $context,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $detailsArray['name'] = $row->getName();
        //M1 > M2 Translation Begin (Rule 9)
        /*$detailsArray['address1'] = $row->getAddress1();
        $detailsArray['address2'] = $row->getAddress2();
        $detailsArray['address3'] = $row->getAddress3();*/
        $detailsArray['address1'] = $row->getData('address1');
        $detailsArray['address2'] = $row->getData('address2');
        $detailsArray['address3'] = $row->getData('address3');
        //M1 > M2 Translation End
        $detailsArray['city'] = $row->getCity();

        $detailsArray['county'] = $row->getCounty();

        $helper = $this->customerconnectHelper;

        $countryCode = $helper->getCountryCodeForDisplay($row->getCountry());
        $region = $helper->getRegionFromCountyName($countryCode, $row->getCounty());
        $countyId = ($region) ? $region->getId() : 0;

        $detailsArray['county_id'] = $countyId;

        $detailsArray['country_code'] = $countryCode;
        $detailsArray['country'] = $row->getCountry();
        $detailsArray['postcode'] = $row->getPostcode();
        $detailsArray['telephone'] = $row->getTelephoneNumber();
        $detailsArray['fax'] = $row->getFaxNumber();
        $detailsArray['address_code'] = $row->getAddressCode();
        $detailsArray['email'] = $row->getEccEmail();
        $jsonArray = json_encode($detailsArray);

        $html = '<input type="text" class="details" name="details"';
        $html .= '" style="display:none" value="' . htmlspecialchars($jsonArray) . '"/> ';
        $html .= $row->getName();
        return $html;
    }

}
