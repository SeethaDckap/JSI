<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\System\Config\Source;


/**
 * Quotes Reminder single note type source model
 * 
 * @category   Epicor
 * @package    Epicor_Quotes
 * @author     Epicor Websales Team
 */
class Singlenotetypes
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'simple', 'label' => 'Simple'),
            array('value' => 'formatted', 'label' => 'Formatted'),
        );
    }

}
