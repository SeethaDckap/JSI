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
class Contractallowed
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'H', 'label' => 'Header Only'),
            array('value' => 'B', 'label' => 'Both Header and Line'),
            array('value' => 'N', 'label' => 'None'),
        );
    }

}
