<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Model\Config\Source;


/**
 * CUAD months dropdown
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Cuadmonths
{

    public function toOptionArray()
    {
        return array(
            array('value' => '', 'label' => ""),
            array('value' => '1', 'label' => '1 Month'),
            array('value' => '2', 'label' => '2 Months'),
            array('value' => '3', 'label' => '3 Months'),
            array('value' => '4', 'label' => '4 Months'),
            array('value' => '5', 'label' => '5 Months'),
            array('value' => '6', 'label' => '6 Months'),
        );
    }

}
