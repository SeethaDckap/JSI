<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Checkoutaddress
{

    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => "Disable"),
            array('value' => 'magentoErp', 'label' => "Update ECC Erp Addresses"),
            array('value' => 'ErpAccount', 'label' => "Update Erp Account Addresses"),
        );
    }

}
