<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Config\Source;


class Lineselection
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'all', 'label' => 'All'),
            array('value' => 'lowest', 'label' => 'Lowest Price'),
            array('value' => 'highest', 'label' => 'Highest Price'),
        );
    }

}
