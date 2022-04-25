<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Locations;


class Gridcolumns
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'erp_code', 'label' => 'ERP Code'),
            array('value' => 'name', 'label' => 'Name'),
            array('value' => 'company', 'label' => 'Company'),
            array('value' => 'address1', 'label' => 'Address 1'),
            array('value' => 'address2', 'label' => 'Address 2'),
            array('value' => 'address3', 'label' => 'Address 3'),
            array('value' => 'city', 'label' => 'City'),
            array('value' => 'county', 'label' => 'County'),
            array('value' => 'country', 'label' => 'Country'),
            array('value' => 'postcode', 'label' => __('Postcode')->render()),
            array('value' => 'telephone_number', 'label' => 'Telephone Number'),
            array('value' => 'fax_number', 'label' => 'Fax Number'),
            array('value' => 'email_address', 'label' => 'Email Address'),
            array('value' => 'sort_order', 'label' => 'Sort Order'),
            array('value' => 'location_visible', 'label' => 'Location Visible'),
            array('value' => 'include_inventory', 'label' => 'Include Inventory'),
            array('value' => 'show_inventory', 'label' => 'Show Inventory'),
        );
    }

}
