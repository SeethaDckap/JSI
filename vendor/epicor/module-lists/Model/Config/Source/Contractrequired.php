<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\Config\Source;


/**
 * List Config actions
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Contractrequired
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'H', 'label' => 'Header'),
            array('value' => 'E', 'label' => 'Either Header or Line'),
            array('value' => 'O', 'label' => 'Optional'),
        );
    }

}
