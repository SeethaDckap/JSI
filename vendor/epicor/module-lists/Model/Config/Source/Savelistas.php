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
class Savelistas
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'Q', 'label' => 'Quick Save'),
            array('value' => 'A', 'label' => 'Advanced Save'),
            array('value' => 'E', 'label' => 'Existing List'),
        );
    }

}
