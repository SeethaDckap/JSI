<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Config\Source;


class Headerselection
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'all', 'label' => 'All'),
            array('value' => 'newest', 'label' => 'Newest'),
            array('value' => 'oldest', 'label' => 'Oldest'),
            array('value' => 'recent', 'label' => 'Most Recently Used'),
        );
    }

}
