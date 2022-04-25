<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\AccessRight\Model\Config\Source;


/**
 * Access Rights 
 *
 * @category   Epicor
 * @package    Epicor_AccessRight
 * @author     Epicor Websales Team
 */
class AccessRightOptions
{

    public function toOptionArray()
    {
        return array(
            array('value' => '2', 'label' => 'Global Default'),
            array('value' => '0', 'label' => 'Disabled'),
            array('value' => '1', 'label' => 'Access Role'),
        );
    }

}
