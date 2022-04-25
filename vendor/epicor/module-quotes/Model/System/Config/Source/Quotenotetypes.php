<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Model\System\Config\Source;


/**
 * Quotes Reminder note type source model
 * 
 * @category   Epicor
 * @package    Epicor_Quotes
 * @author     Epicor Websales Team
 */
class Quotenotetypes
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'disabled', 'label' => 'Disabled'),
            array('value' => 'single', 'label' => 'Single Note'),
            array('value' => 'multiple', 'label' => 'Multiple Notes'),
        );
    }

}
