<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


/**
 * Epicor_Comm_Model_Config_Source_Bsvrepricing
 * 
 * Builds array for BSV address order
 */
class Bsvaddressorder
{

    public function toOptionArray()
    {
        return array(
            'customer' => 'Customer then ERP',
            'erp' => 'ERP then customer',
        );
    }

}
