<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Address\Renderer;


class Address extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    //   protected $updateList = Array();
    public function render(\Magento\Framework\DataObject $row)
    {
        $detailsArray['id'] = $row->getEntityId();
        $detailsArray['company'] = $row->getCompany();
        $detailsArray['street'] = $row->getStreet();
        $detailsArray['city'] = $row->getCity();
        $detailsArray['region'] = $row->getRegion();
        $detailsArray['country'] = $row->getCountry();
        $detailsArray['postcode'] = $row->getPostcode();
        $jsonArray = json_encode($detailsArray);

        $html = '<input type="text" class="details" name="details"';
        $html .= '" style="display:none" value="' . htmlspecialchars($jsonArray) . '"/> ';
        $html .= $row->getId();
        return $html;
    }

}
