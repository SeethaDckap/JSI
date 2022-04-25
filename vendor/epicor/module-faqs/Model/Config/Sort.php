<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Model\Config;


/**
 * F.A.Q. Sorting field options
 * 
 * @category   Epicor
 * @package    Faq
 * @author     Epicor Websales Team
 * 
 * @method   array(string)  toOptionArray()
 */
class Sort
{

    public function toOptionArray()
    {
        return array(
            'usefulness' => 'Rating',
            'weight' => 'Weight'
        );
    }

}
