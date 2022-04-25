<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class DefaultPackingBasis
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => ' '),
            array('value' => 'D', 'label' => 'D'),
        );
    }

}
