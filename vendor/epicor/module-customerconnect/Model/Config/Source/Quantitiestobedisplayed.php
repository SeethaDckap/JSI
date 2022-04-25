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
class Quantitiestobedisplayed
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'quantities', 'label' => 'Ordered'),
            array('value' => 'delivered', 'label' => 'Shipped'),
            array('value' => 'to_follow', 'label' => 'To Follow'),
        );
    }

}
