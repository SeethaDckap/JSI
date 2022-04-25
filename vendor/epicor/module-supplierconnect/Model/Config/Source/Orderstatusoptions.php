<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Config\Source;


/**
 * Order status options for grids
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Orderstatusoptions
{

    public function toGridArray()
    {
        return array(
            'O' => 'Open',
            'C' => 'Closed'
        );
    }

}
