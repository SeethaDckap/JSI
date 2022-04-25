<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\System\Config\Source;


/**
 * Quotes Reminder email option source model
 * 
 * @category   Epicor
 * @package    Epicor_Quotes
 * @author     Epicor Websales Team
 */
class Reminders
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'none', 'label' => 'None'),
            array('value' => 'admin', 'label' => 'Admin Only'),
            array('value' => 'customer', 'label' => 'Customer Only'),
            array('value' => 'both', 'label' => 'Both Admin and Customer'),
        );
    }

}
