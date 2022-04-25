<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Config\Source;


/**
 * RFQ status options for grids
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Rfqstatusoptions
{

    public function toGridArray()
    {
        return array(
            'O' => 'Open',
            'C' => 'Closed'
        );
    }

}
