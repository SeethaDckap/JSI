<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\Config\Source;


class RoleSetting
{
    protected $_roleSetting = [
        ['value' => '', 'label' => 'None'],
        ['value' => 'access_role', 'label' => 'Access Role']
    ];

    public function toOptionArray()
    {
        return $this->_roleSetting;
    }

}
