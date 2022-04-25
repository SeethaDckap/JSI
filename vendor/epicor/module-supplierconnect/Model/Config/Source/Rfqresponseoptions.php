<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Model\Config\Source;


/**
 * RFQ response options for grids
 *
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Rfqresponseoptions
{

    public function toGridArray()
    {
        return array(
            'Waiting' => 'Waiting',
            'Not Waiting' => 'Not Waiting',
            'Received' => 'Received',
            'Accepted' => 'Accepted',
            'Closed' => 'Closed'
        );
    }

}
