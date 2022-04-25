<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Locationstockvisibility
{

    public function toOptionArray()
    {

        return array(
            array('value' => 'default', 'label' => 'Default Locations'),
            array('value' => 'logged_in_shopper_source', 'label' => 'Logged In Shopper Source Location'),
            array('value' => 'all_source_locations', 'label' => 'All Source Locations'),
            array('value' => 'all_given_company', 'label' => 'All Locations for a Given Company'),
            array('value' => 'locations_to_include', 'label' => 'List of Specific Locations to Include'),
            array('value' => 'locations_to_exclude', 'label' => 'List of Specific Locations to Exclude'),
        );
    }

}
