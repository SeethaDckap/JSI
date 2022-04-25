<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Returns;


class Findreturnsby
{

    const FIND_BY_RETURN_NUMBER = 'return_number';
    const FIND_BY_CASE_MANAGEMENT_NUMBER = 'case_number';
    const FIND_BY_CUSTOMER_REFERENCE = 'customer_ref';

    public function toOptionArray()
    {
        return array(
            array('value' => self::FIND_BY_RETURN_NUMBER, 'label' => 'Return Number'),
            array('value' => self::FIND_BY_CASE_MANAGEMENT_NUMBER, 'label' => 'Case Management Number'),
            array('value' => self::FIND_BY_CUSTOMER_REFERENCE, 'label' => 'Customer Reference'),
        );
    }

}
