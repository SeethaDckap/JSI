<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Model\Config\Source;


class Erpyesno
{

    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => 'No, but allow ERP Account Level Setting'),
            array('value' => '1', 'label' => 'Yes, but allow ERP Account Level Setting'),
            array('value' => 'forceyes', 'label' => 'Force Yes, for all ERP Accounts'),
            array('value' => 'forceno', 'label' => 'Force No, for all ERP Accounts'),
        );
    }

}
