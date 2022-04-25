<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Fsubfrequency
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'instant', 'label' => "Instant - Files Submitted on Upload"),
            array('value' => 'scheduled', 'label' => "Scheduled - Files Submitted by Scheduled Process"),
        );
    }

}
