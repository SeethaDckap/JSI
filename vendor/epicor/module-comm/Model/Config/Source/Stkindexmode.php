<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


/**
 * STK - index mode options
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Stkindexmode
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'none', 'label' => 'Disabled'),
            array('value' => 'scheduled', 'label' => 'Scheduled'),
            array('value' => 'instant', 'label' => 'Instant'),
        );
    }

}
