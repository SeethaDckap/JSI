<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source\Returns;


class Skutype
{

    const ERP_SKU = 'erp';
    const CUSTOM_SKU = 'custom';

    public function toOptionArray()
    {
        return array(
            array('value' => self::ERP_SKU, 'label' => 'ERP SKU'),
            array('value' => self::CUSTOM_SKU, 'label' => 'Custom SKU'),
        );
    }

}
